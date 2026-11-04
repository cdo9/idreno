<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Pose;
use App\Services\CommerciauxService;

class Poses extends Component
{
    public $dateDebut;
    public $dateFin;
    public $commercial = null;
    public $lstCommerciaux = [];
    public $lstAllCommerciaux = [];
    public $poses = [];
    public $poseurs = [];
    public $isDirecteur = false;
    public $isTelepro = false;
    public $isAdmin = false;
    public $isAdministratif = false;

    public function mount()
    {
        $user = Auth::user();
        $userId = Auth::id();

        $this->dateDebut = request('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $this->dateFin = request('date_fin', now()->endOfMonth()->format('Y-m-d'));

        $this->isDirecteur = $user?->hasRole('directeur') ?? false;
        $this->isTelepro = $user?->hasRole('telepro') ?? false;
        $this->isAdmin = $user?->hasRole('admin') ?? false;
        $this->isAdministratif = $user?->hasRole('administratif') ?? false;

        
        $listeComm = CommerciauxService::getEntireCommerciauxListWithInactive();

        foreach($listeComm as $commercial){
            $this->lstAllCommerciaux[$commercial->id] = $commercial;
        }

        $listeComm = CommerciauxService::getCommerciauxListBySector();

        foreach($listeComm as $commercial){
			if($commercial->role != "telepro" && $commercial->role != "resp-prosp")
				$this->lstCommerciaux[$commercial->id] = $commercial;
        }
        

        if ($this->isDirecteur || $this->isTelepro || $this->isAdministratif) {
            $this->commercial = request('commercial', null);
        } else {
            $this->commercial = $userId;
        }

        $this->loadPoseurs();
        $this->loadData();
    }

    private function loadPoseurs()
    {
        $poseurs = DB::table('poseurs')->get();
        $this->poseurs = ['--'];
        foreach ($poseurs as $poseur) {
            $this->poseurs[$poseur->id] = $poseur->poseur;
        }
    }
    public function getSuiviPercentage(string $field): int
    {
        if ($this->poses->count() === 0) {
            return 0;
        }

        $count = $this->poses->where($field, 1)->count();
        return round(($count / $this->poses->count()) * 100);
    }

    private function loadData()
    {
        $poses = Pose::with([
            'client:id,nom,prenom,adresse,cp,ville,telephone1',
            'vente:id_commercial,id_commercial_2,id_commercial_3',
            'poseurs:id'
        ])
        ->select([
            'poses.*',
            DB::raw('GROUP_CONCAT(pose_details.id_poseur) as poseurs')
        ])
        ->leftJoin('pose_details', 'poses.id', '=', 'pose_details.id_pose')
        ->whereBetween('poses.date_debut', [$this->dateDebut, $this->dateFin])
        ->where('poses.secteur', session('selectedSecteur'));
        if ($this->commercial) {
            $poses = $poses->where(function($q) {
                $q->whereHas('vente', function($vente) {
                    $vente->where('id_commercial', $this->commercial)
                        ->orWhere('id_commercial_2', $this->commercial)
                        ->orWhere('id_commercial_3', $this->commercial);
                })
                ->orWhere('id_commercial_visite', $this->commercial);
            });
        }
        $poses = $poses->groupBy('poses.id')
        ->orderBy('poses.date_debut')
        ->get();

        $this->poses = $poses->map(function ($pose) {
            if ($pose->visite_chantier == 1 && $pose->avis_client == 1 && $pose->avis_facebook == 1) {
                $classRow = 'valid_vente';
            } elseif ($pose->visite_chantier == 1 || $pose->avis_client == 1 || $pose->avis_facebook == 1) {
                $classRow = 'attente_vente';
            } else {
                $classRow = 'annule_vente';
            }

            // Récupération des poseurs pour cette pose
            $poseursPose = explode(',', $pose->poseurs);
            foreach($poseursPose as $poseur){
                $poseursNoms[] = $this->poseurs[$poseur]?? '--';
            }

            // Récupération des commerciaux
            $commerciauxIds = array_filter([
                $pose->id_commercial ?? 0,
                $pose->id_commercial_2 ?? 0,
                $pose->id_commercial_3 ?? 0,
                $pose->id_commercial_visite ?? 0,
            ]);

            $commerciauxPose = [];

            if (!empty($commerciauxIds)) {
                foreach(array_unique($commerciauxIds) as $id){
                    $commerciauxPose[] = $this->lstAllCommerciaux[$id];
                }
            }

            return [
                'id_pose' => $pose->id,
                'nom' => $pose->client->nom,
                'prenom' => $pose->client->prenom,
                'adresse' => $pose->client->adresse,
                'cp' => $pose->client->cp,
                'ville' => $pose->client->ville,
                'telephone' => $pose->client->telephone1,
                'date_debut' => Carbon::parse($pose->date_debut)->format('d/m/Y'),
                'date_fin' => Carbon::parse($pose->date_fin)->format('d/m/Y'),
                'date_debut_raw' => $pose->date_debut,
                'date_fin_raw' => $pose->date_fin,
                'produit' => $pose->produit,
                'produit2' => $pose->produit2,
                'produit3' => $pose->produit3,
                'produits' => array_filter([$pose->produit, $pose->produit2, $pose->produit3], 'strlen'),
                'visite_chantier' => $pose->visite_chantier,
                'avis_client' => $pose->avis_client,
                'avis_facebook' => $pose->avis_facebook,
                'indicateur' => $pose->indicateur,
                'class_row' => $classRow,
                'poseurs' => $poseursNoms,
                'commerciaux' => $commerciauxPose,
                'id_commercial_visite' => $pose->id_commercial_visite,
                'google_calendar_url' => $this->generateGoogleCalendarUrl($pose)
            ];
        });
    }

    private function generateGoogleCalendarUrl($pose)
    {
        $url = "http://www.google.com/calendar/event";
        $url .= "?action=TEMPLATE";
        $url .= "&dates=" . date('Ymd', strtotime($pose->date_debut));
        $url .= "/" . date('Ymd', strtotime($pose->date_fin . " +1day"));
        $url .= "&text=" . urlencode($pose->nom);
        $url .= "&location=" . urlencode($pose->adresse . ' ' . $pose->cp . ' ' . $pose->ville);
        $url .= "&details=" . urlencode(implode("\n", [$pose->telephone1, $pose->produit, $pose->produit2, $pose->produit3]));

        return $url;
    }

    public function updated()
    {
        $this->loadData();
    }

    public function updateFilter()
    {
        $this->loadData();
    }

    public function toggleVisiteChantier($idPose, $newValue)
    {
        if (!$this->isAdmin) return;

        DB::table('poses')
            ->where('id', $idPose)
            ->update(['visite_chantier' => $newValue]);

        $this->loadData();
    }

    public function toggleAvisClient($idPose, $newValue)
    {
        if (!$this->isAdmin) return;

        DB::table('poses')
            ->where('id', $idPose)
            ->update(['avis_client' => $newValue]);

        $this->loadData();
    }

    public function toggleAvisFacebook($idPose, $newValue)
    {
        if (!$this->isAdmin) return;

        DB::table('poses')
            ->where('id', $idPose)
            ->update(['avis_facebook' => $newValue]);

        $this->loadData();
    }

    public function toggleIndicateur($idPose, $newValue)
    {
        if (!$this->isAdmin) return;

        DB::table('poses')
            ->where('id', $idPose)
            ->update(['indicateur' => $newValue]);

        $this->loadData();
    }

    public function getIndicateurs()
    {
        $cptPosesIndicateur = 0;
        $nbVisites = 0;
        $nbAvis = 0;
        $nbFacebook = 0;

        foreach ($this->poses as $pose) {
            if ($pose['indicateur'] == 0) continue;
            $cptPosesIndicateur += 1;
            $nbVisites += $pose['visite_chantier'];
            $nbAvis += $pose['avis_client'];
            $nbFacebook += $pose['avis_facebook'];
        }

        return [
            'visite_pct' => $cptPosesIndicateur > 0 ? round(100 * $nbVisites / $cptPosesIndicateur) : 0,
            'avis_pct' => $cptPosesIndicateur > 0 ? round(100 * $nbAvis / $cptPosesIndicateur) : 0,
            'facebook_pct' => $cptPosesIndicateur > 0 ? round(100 * $nbFacebook / $cptPosesIndicateur) : 0,
        ];
    }

    public function getCommercialNom($commercial)
    {
        return trim(($commercial->prenom ?? '') . ' ' . ($commercial->nom ?? ''));
    }

    public function confirmDelete($poseId){
        $pose = Pose::find($poseId);
        if ($pose) {
            DB::table('pose_details')->where('id_pose', $poseId)->delete();
            $pose->delete();
            session()->flash('message', 'Pose supprimée avec succès.');
            $this->loadData();
            // Optionnel : recharger la liste ou rediriger
        } else {
            session()->flash('error', 'Pose non trouvée.');
        }
    }

    public function render()
    {
        return view('livewire.poses', [
            'indicateurs' => $this->getIndicateurs()
        ]);
    }
}
