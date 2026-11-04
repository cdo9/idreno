<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\Projet;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\CommerciauxService;


class RendezVous extends Component
{
    public $m;
    public $y;
    public $commercial = null;
    public $rdvs = [];
    public $lstCommerciaux = [];
    public $message = null;
    public $isResponsable = false;
    public $isDirecteur = false;
    public $isAdministratif = false;
    public $countCancel = 0;
    public $countConfirmed = 0;
    public $countToConfirm = 0;
    public $countRescheduled = 0;

    public $selectedClientId = null;

    protected $queryString = ['y', 'm', 'commercial'];

    public function mount()
    {
        $user = Auth::user();
        $userId = Auth::id();

        $this->m = $this->m ?? request('m', now()->format('n'));
        $this->y = $this->y ?? request('y', now()->format('Y'));

        $this->isDirecteur = $user?->hasRole('directeur') ?? false;
        $this->isAdministratif = $user?->hasRole('administratif') ?? false;
        $this->isResponsable = DB::table('groupes')
            ->where('id_responsable', $userId)
            ->exists();

        if ($this->isDirecteur || $this->isAdministratif) {
            $this->commercial = $this->commercial ?? request('commercial', null);
            $this->lstCommerciaux = CommerciauxService::getCommerciauxListBySector('date_entree','asc');

        } elseif ($this->isResponsable) {
            $this->commercial = $this->commercial ?? request('commercial', null);


            $groupeResponsable = DB::table('groupes')
                ->where('id_responsable', $userId)
                ->value('id');

            if ($groupeResponsable) {
                $this->lstCommerciaux = DB::table('users')
                    ->where('active', 1)
                    ->where('groupe', $groupeResponsable)
                    ->orderBy('nom')
                    ->get();
            } else {
                $this->lstCommerciaux = collect();
            }

        } else {
            $this->commercial = $this->commercial ?? $userId;
            $this->lstCommerciaux = collect();
        }

        $this->loadData();
    }

    public function updated()
    {
        $this->loadData();
    }

    private function loadData()
    {
        if ($this->m == 0) {
            $dateDebut = Carbon::createFromDate($this->y, 1, 1)->startOfYear()->toDateString();
            $dateFin   = Carbon::createFromDate($this->y, 12, 31)->endOfYear()->toDateString();
        } else {
            $dateDebut = Carbon::createFromDate($this->y, $this->m, 1)->startOfMonth()->toDateString();
            $dateFin   = Carbon::createFromDate($this->y, $this->m, 1)->endOfMonth()->toDateString();
        }

        $query = Projet::with('client:id,nom,prenom,telephone1,ville,source,rentre_par')
            ->leftJoin('clients as c', 'c.id', '=', 'projets.id_client')
            ->leftJoin('sources as s', 'c.id_source', '=', 's.id')
            ->select(
                'projets.id',
                'projets.date',
                'projets.date_ajout',
                'projets.date_confirmation',
                'projets.heure',
                'projets.rdv_confirme',
                'projets.rdv_annule',
                'projets.projet',
                'projets.projet2',
                'projets.projet3',
                'projets.id_client',
                'projets.partage_avec as partage_id',
                's.nom as source_name'
            );

        if($this->selectedClientId == null)
            $query->whereBetween('projets.date', [$dateDebut, $dateFin]);



        $currentUserId = Auth::id();

        if($this->selectedClientId == null){
			
			$query->where('projets.secteur', '=', session('selectedSecteur'));
			
            if ($this->isDirecteur || $this->isAdministratif) {
                if ($this->commercial) {
                    $query->where(function ($q) {
                        $q->where('c.rentre_par', $this->commercial)
                            ->orWhere('projets.partage_avec', $this->commercial);
                    });
                }

            } /*elseif ($this->isResponsable) {
                $groupeResponsable = DB::table('groupes')
                    ->where('id_responsable', $currentUserId)
                    ->value('id');

                if ($groupeResponsable) {
                    $query->where(function ($q) use ($groupeResponsable) {
                        $q->whereIn('c.rentre_par', function ($sub) use ($groupeResponsable) {
                            $sub->select('id')->from('users')->where('groupe', $groupeResponsable)->where('active', 1);
                        })->orWhereIn('p.partage_avec', function ($sub) use ($groupeResponsable) {
                            $sub->select('id')->from('users')->where('groupe', $groupeResponsable)->where('active', 1);
                        });
                    });

                    if ($this->commercial) {
                        $query->where(function ($q) {
                            $q->where('c.rentre_par', $this->commercial)
                                ->orWhere('p.partage_avec', $this->commercial);
                        });
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            } */else {
                $query->where(function ($q) use ($currentUserId) {
                    $q->where('c.rentre_par', $currentUserId)
                    ->orWhere('projets.partage_avec', $currentUserId);
                });
            }
        }else{
            $query->where("c.id", $this->selectedClientId);
        }

        $rows = $query->orderBy('projets.date', 'asc')->get();
        
        $this->countCancel = 0;
        $this->countConfirmed = 0;
        $this->countToConfirm = 0;
        $this->countRescheduled = 0;

        $this->rdvs = $rows->map(function ($rdv) {
            if($rdv->rdv_annule){
                $this->countCancel++;
            }elseif($rdv->rdv_confirme == 1){
                $this->countConfirmed++;
            }elseif($rdv->rdv_confirme == 2){
                $this->countRescheduled++;
            }else{
                $this->countToConfirm++;
            }
            return [
                'id'                => $rdv->id,
                'date'              => Carbon::parse($rdv->date)->format('d/m/Y'),
                'client'            => $rdv->client->nom . ' ' . $rdv->client->prenom,
                'etat'              => $rdv->rdv_confirme,
                'annule'            => $rdv->rdv_annule,
                'date_confirmation' => $rdv->date_confirmation ? Carbon::parse($rdv->date_confirmation)->format('d/m/Y'): null,
                'heure' 			=> $rdv->heure,
                'date_priseRDV'     => Carbon::parse($rdv->date_ajout)->format('d/m/Y'),
                'telephone'         => $rdv->client->telephone1_formatted,
                'ville'             => $rdv->client->ville,
                'projets'           => trim(($rdv->projet ?? '') . '<br>' . ($rdv->projet2 ?? '') . '<br>' . ($rdv->projet3 ?? '')),
                'source'            => $rdv->source_name,
                'commercial'        => CommerciauxService::getCommercialById($rdv->client->rentre_par),
                'partage'           => CommerciauxService::getCommercialById($rdv->partage_id),
                'agendaLink'        => $this->generateCalendarLink($rdv)
            ];
        });
    }

    public function reactiver($id)
    {
        DB::table('projets')
            ->where('id', $id)
            ->update(['rdv_annule' => 0]);

        $this->loadData();
        $this->message = "Rendez-vous réactivé avec succès ✅";
    }


    public function confirmer($id)
    {
        DB::table('projets')
            ->where('id', $id)
            ->update(['rdv_confirme' => 1, 'date_confirmation' => Carbon::now()]);

        $this->loadData();
        $this->message = "Rendez-vous confirmé avec succès ✅";
    }

    public function repositionner($id)
    {
        DB::table('projets as p')
            ->join('clients as c', 'p.id_client', '=', 'c.id')
            ->where('p.id', $id)
            ->update(['p.rdv_confirme' => 2, 'p.date_confirmation' => null, 'c.type' => 0]);

        $this->loadData();
        $this->message = "Rendez-vous marqué à repositionner ✅";
    }

    public function annuler($id)
    {
        DB::table('projets as p')
            ->join('clients as c', 'p.id_client', '=', 'c.id')
            ->where('p.id', $id)
            ->update([
                'p.rdv_annule' => 1,
                'c.type' => 0  
            ]);

        $this->loadData();
        $this->message = "Rendez-vous annuler avec succès";

    }

    public static function generateCalendarLink($rdv){
        $url = "http://www.google.com/calendar/event";
        $url .= "?action=TEMPLATE";
        $heure = !empty($rdv->heure) ? $rdv->heure : "09:00";
        $date = $rdv->date . ' ' . $heure;
        $date_rdv = Carbon::parse($rdv->date)->setTimeFromTimeString($heure);
        
        $timestamp = $date_rdv->timestamp;     

        $date_realisation_du_projet = null;
        if(isset($rdv->date_realisation_du_projet)){
            switch($rdv->date_realisation_du_projet){
                case 1:
                    $date_realisation_du_projet = "< 6 mois";
                    break;
                case 2:
                    $date_realisation_du_projet = "entre 6 mois et 1 an";
                    break;
                case 3:
                    $date_realisation_du_projet = "> 1 an";
                    break;
            }
        }

        $url .= "&dates=".date('Ymd\THi00', $timestamp);
        $url .= "/".date('Ymd\THi00', $timestamp + 7200);
        $url .= "&text=".urlencode($rdv->client->nom ?? 'Client');
        $url .= "&location=".urlencode(($rdv->client->adresse ?? '').' '.($rdv->client->cp ?? '').' '.($rdv->client->ville ?? ''));
        $url .= "&details=" . urlencode(($rdv->client->telephone1 ?? '')."\n".($rdv->client->telephone2 ?? '')."\n".($rdv->projet ?? '')."\n Date réalisation projet :". $date_realisation_du_projet ?? 'Non renseigné');
        return $url;
    }


    public function render()
    {
        $years = range(2014, now()->year);
        $months = [
            'Janvier' => '1', 'Février' => '2', 'Mars' => '3',
            'Avril' => '4', 'Mai' => '5', 'Juin' => '6',
            'Juillet' => '7', 'Août' => '8', 'Septembre' => '9',
            'Octobre' => '10', 'Novembre' => '11', 'Décembre' => '12',
        ];
        return view('livewire.rendez-vous', [
            'years' => $years,
            'months' => $months,
            'rdvs' => $this->rdvs,
            'lstCommerciaux' => $this->lstCommerciaux,
        ]);
    }
}
