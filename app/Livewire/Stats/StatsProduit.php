<?php

namespace App\Livewire\Stats;

use App\Services\ProjectService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StatsProduit extends Component
{
    public $dateDebut;

    public $dateFin;

    public $commercial = '';

    public $commerciaux = [];

    public $statsProduits = [];

    public $caTotal = 0;

    public $isDirecteur;

    public function mount()
    {
        $user = Auth::user();

        $this->dateDebut = $this->dateDebut ?: Carbon::now()->startOfMonth()->toDateString();
        $this->dateFin = $this->dateFin ?: Carbon::now()->endOfMonth()->toDateString();

        $this->isDirecteur = $user->hasRole('directeur');

        if ($this->isDirecteur) {
            // Directeur → peut choisir
            $this->commercial = request('commercial', '');
        } else {
            // Pas directeur → forcé sur lui-même
            $this->commercial = $user->id;
        }

        if ($this->isDirecteur) {
            // Directeur → peut voir tous les commerciaux
            $this->commerciaux = DB::table('users')
                ->where('active', 1)
                ->get()
                ->toArray();
        } else {
            // Commercial classique → ne peut voir que les commerciaux de ses secteurs
            $userSecteurs = DB::table('secteur_commercials')
                ->where('id_commercial', $user->id)
                ->pluck('id_secteur')
                ->toArray();

            if (! empty($userSecteurs)) {
                $commercialsSameSecteur = DB::table('secteur_commercials')
                    ->whereIn('id_secteur', $userSecteurs)
                    ->pluck('id_commercial')
                    ->unique()
                    ->toArray();

                $this->commerciaux = DB::table('users')
                    ->whereIn('id', $commercialsSameSecteur)
                    ->where('active', 1)
                    ->get()
                    ->toArray();
            } else {
                // Si l'utilisateur n'a pas de secteur, il ne voit que lui-même
                $this->commerciaux = DB::table('users')
                    ->where('id', $user->id)
                    ->where('active', 1)
                    ->get()
                    ->toArray();
            }
        }

        $this->loadStats();
    }

    public function updated($property)
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $cId = $this->commercial ?: null;

        $dateDebut = Carbon::parse($this->dateDebut)->startOfDay();
        $dateFin = Carbon::parse($this->dateFin)->endOfDay();

        // Récupération des ventes
        $ventes = DB::table('ventes as v')
            ->leftJoin('rendez_vous as rv', 'v.id_rdv', '=', 'rv.id')
            ->whereBetween('v.date', [$dateDebut, $dateFin])
            ->where('v.statut', '!=', 3)
            ->select('v.*', 'rv.produits')
            ->get();

        $stats = [];

        foreach ($ventes as $vente) {
            $slugs = explode(';', $vente->produits ?? '');
            foreach ($slugs as $slug) {
                if (! isset($stats[$slug])) {
                    $stats[$slug] = [
                        'ca_vendeur' => 0,
                        'ca_total' => 0,
                    ];
                }

                $part = $vente->ca_ht / max(1, count($slugs));

                // ca_total toujours incrémenté
                $stats[$slug]['ca_total'] += $part;

                // ca_vendeur seulement si un commercial sélectionné correspond
                if ($cId && $vente->id_commercial == $cId) {
                    $stats[$slug]['ca_vendeur'] += $part;
                }
            }
        }

        $lstProduits = ProjectService::listPriorities();

        $this->statsProduits = [];
        foreach ($lstProduits as $famille => $produits) {
            foreach ($produits as $slug => $produit) {
                $this->statsProduits[$famille][$slug] = [
                    'label' => $produit,
                    'ca_vendeur' => $stats[$slug]['ca_vendeur'] ?? 0,
                    'ca_total' => $stats[$slug]['ca_total'] ?? 0,
                ];
            }
        }

        $this->caTotal = collect($this->statsProduits)
            ->flatten(1)
            ->sum('ca_total');
    }

    public function getCommercialNom($commercial): string
    {
        $prenom = $commercial->prenom ?? '';
        $nom = $commercial->nom ?? '';

        return trim($prenom.' '.$nom);
    }

    public function render()
    {
        return view('livewire.stats.stats-produit');
    }
}
