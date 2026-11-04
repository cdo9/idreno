<?php

namespace App\Livewire;

use App\Services\ProjectService;
use Livewire\Component;
use App\Models\Pose;
use App\Models\Vente;
use App\Models\Poseur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\CommerciauxService;

class NouvellePose extends Component
{
    public $pose = [];
    public $ventes = [];
    public $poseurs = [];
    public $commerciaux = [];
    public $mode = 'creation';
    public $poseId = null;
    public $lstPriorities;
    public $searchVente = '';
    public $selectedVente = null;

    public $url = null;

    public function mount($poseId = null)
    {
        $this->poseId = $poseId;

        $this->ventes = collect();
        $this->poseurs = Poseur::where('archive', 0)->get();
        $this->commerciaux = CommerciauxService::getCommerciauxListBySector();
        $this->lstPriorities = ProjectService::listPriorities();

        $this->pose = [
            'date_debut' => now()->format('Y-m-d'),
            'date_fin' => now()->format('Y-m-d'),
            'poseurs' => [],
            'produit' => null,
            'produit2' => '',
            'produit3' => '',
            'visite_chantier' => 0,
            'avis_client' => 0,
            'id_commercial_visite' => null,
            'id_vente' => null,
            'secteur' => session('selectedSecteur'),
            'id_client' => null,
        ];

        // Charger la pose existante si un ID est fourni
        if ($this->poseId) {
            $pose = Pose::with(['poseurs','client'])->find($this->poseId);
            if ($pose) {
                $this->pose = [
                    'date_debut' => optional($pose->date_debut)->format('Y-m-d'),
                    'date_fin' => optional($pose->date_fin)->format('Y-m-d'),
                    'poseurs' => $pose->poseurs->pluck('id')->toArray(),
                    'produit' => $pose->produit,
                    'produit2' => $pose->produit2 ?? '',
                    'produit3' => $pose->produit3 ?? '',
                    'visite_chantier' => $pose->visite_chantier,
                    'avis_client' => $pose->avis_client,
                    'id_commercial_visite' => $pose->id_commercial_visite,
                    'id_vente' => $pose->id_vente,
                    'secteur' => session('selectedSecteur'),
                    'id_client' => $pose->id_client,
                    'searchVente' => $pose->client->nom . ' ' . $pose->client->prenom
                ];
                $this->searchVente = $pose->client->nom . ' ' . $pose->client->prenom;

                $this->mode = 'modification';
            }
        }
    }

    public function save()
    {
        // Validation
        $validated = $this->validate([
            'pose.date_debut' => 'required|date',
            'pose.date_fin' => 'required|date',
            'pose.id_vente' => 'required|integer',
            'pose.produit' => 'required|string',
            'pose.visite_chantier' => 'required|boolean',
            'pose.avis_client' => 'required|boolean',
        ]);

        // Forcer le format des dates pour Eloquent
        $this->pose['date_debut'] = Carbon::parse($this->pose['date_debut'])->format('Y-m-d');
        $this->pose['date_fin'] = Carbon::parse($this->pose['date_fin'])->format('Y-m-d');

        // Récupérer l'id_client depuis la vente sélectionnée
        $this->url = "";
        if ($this->pose['id_vente']) {
            $vente = Vente::with('client')->find($this->pose['id_vente']);
            if ($vente) {
                $this->pose['id_client'] = $vente->id_client;

                $this->url = "http://www.google.com/calendar/event";
                $this->url .= "?action=TEMPLATE";
                $this->url .= "&dates=" . Carbon::parse($this->pose['date_debut'])->format('Ymd');
                $this->url .=       "/" . Carbon::parse($this->pose['date_fin'])->addDays(1)->format('Ymd');
                $this->url .= "&text=" . urlencode($vente->client->nom . " - visite de pose - tap sur pose");
                $this->url .= "&location=".urlencode($vente->client->adresse.' '. $vente->client->cp .' '. $vente->client->ville);
                $this->url .= "&details=" . urlencode($this->pose['produit']);

            }
        }

        // Création ou modification
        if ($this->mode === 'creation') {
            $pose = Pose::create($this->pose);
        } else {
            $pose = Pose::find($this->poseId);
            $pose->update($this->pose);
        }

        // Synchroniser les poseurs
        $pose->poseurs()->sync($this->pose['poseurs'] ?? []);

        
        session()->flash('message', 'Pose enregistrée avec succès.');
        //return redirect()->route('poses');
    }

    public function render()
    {
        return view('livewire.nouvelle-pose');
    }

    public function updatedSearchVente()
    {
        $search = $this->searchVente;
        // Requête filtrée selon la recherche
        $query = DB::table('ventes as v')
        ->select(DB::raw('MIN(v.id) as id_vente'), 'v.date', 'c.nom', 'c.prenom', 'c.adresse', 'c.ville', 'c.cp', 'c.id')
        ->leftJoin('clients as c', 'v.id_client', '=', 'c.id')
        ->leftJoin('poses as p', 'p.id_vente', '=', 'v.id')
        ->whereNull('p.id');

        if($search){
            $query = $query->where(function($query) use ($search) {
                $query->where('c.nom', 'like', "%$search%")
                    ->orWhere('c.prenom', 'like', "%$search%");
            });
        }
        $this->ventes = $query->where('v.statut', '=', 2)
        ->groupBy('v.date', 'c.id',  'c.nom', 'c.prenom', 'c.adresse', 'c.ville', 'c.cp')
        ->limit(10)->get();
    }

    public function selectVente($id)
    {
        $this->pose['id_vente'] = $id;
        $this->searchVente = $this->ventes->firstWhere('id_vente', $id)->prenom . ' ' . $this->ventes->firstWhere('id_vente', $id)->nom;
        $this->ventes = [];
    }
}
