<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\CommerciauxService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Argumentations extends Component
{
    public $m;

    public $y;

    public $commercial = null;

    public $argus = [];

    public $lstCommerciaux = [];

    public $message = null;

    public $isResponsable = false;

    public $isDirecteur = false;

    public $isAdmin = false;

    public $isAdministratif = false;

    public $selectedClientId = null;

    protected $queryString = ['y', 'm', 'commercial'];

    public function mount()
    {
        $user = Auth::user();
        $userId = Auth::id();

        $this->m = (int) ($this->m ?? request('m', now()->format('m')));
        $this->y = $this->y ?? request('y', now()->format('Y'));

        // Détection des rôles
        $this->isDirecteur = $user?->hasRole('directeur') ?? false;
        $this->isAdmin = $user?->hasRole('admin') ?? false;
        $this->isAdministratif = $user?->hasRole('administratif') ?? false;
        $this->isResponsable = DB::table('groupes')
            ->where('id_responsable', $userId)
            ->exists();

        $this->commercial = $this->commercial ?? request('commercial', null);
        if ($this->isAdministratif) {
            $this->lstCommerciaux = CommerciauxService::getEntireCommerciauxList('date_entree', 'asc');
        } elseif ($this->isDirecteur) {
            $this->lstCommerciaux = CommerciauxService::getCommerciauxListBySector('date_entree', 'asc');
        } elseif ($this->isResponsable) {

            $groupeResponsable = DB::table('groupes')
                ->where('id_responsable', $userId)
                ->value('id');

            if ($groupeResponsable) {
                $this->lstCommerciaux = User::where('active', 1)
                    ->where('groupe', $groupeResponsable)
                    ->orderBy('date_entree')
                    ->get();
            } else {

                $this->lstCommerciaux = User::where('id', $userId)->get();
            }

        } else {
            $this->commercial = $this->commercial ?? $userId;

            $this->lstCommerciaux = User::where('id', $userId)->get();
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
            $dateFin = Carbon::createFromDate($this->y, 12, 31)->endOfYear()->toDateString();
        } else {
            $dateDebut = Carbon::createFromDate($this->y, $this->m, 1)->startOfMonth()->toDateString();
            $dateFin = Carbon::createFromDate($this->y, $this->m, 1)->endOfMonth()->toDateString();
        }

        $query = DB::table('rendez_vous as rv')
            ->leftJoin('ventes as v', function ($join) {
                $join->on('rv.id', '=', 'v.id_rdv')
                    ->on('rv.id_commercial', '=', 'v.id_commercial');
            })
            ->leftJoin('clients as c', 'c.id', '=', 'rv.id_client')
            ->leftJoin('users as commercial', 'commercial.id', '=', 'rv.id_commercial')
            ->leftJoin('users as partage', 'partage.id', '=', 'rv.partage_avec')
            ->leftJoin('users as com3', 'com3.id', '=', 'v.id_commercial_3')
            ->select(
                'rv.id',
                'rv.date',
                'rv.devis_total_ht',
                'rv.tarif_total_ht',
                'rv.resultat',
                'rv.commentaire',
                'v.ca_ht',
                'v.tarif_ht',
                'v.statut',
                'c.nom',
                'c.prenom',
                'commercial.id as commercial_id',
                'partage.id as partage_id',
                'com3.id as com3_id',
            );

        if ($this->selectedClientId == null) {
            $query->whereBetween('rv.date', [$dateDebut, $dateFin]);
        }

        $currentUserId = Auth::id();

        if ($this->selectedClientId == null) {

            $query->where('rv.secteur', '=', session('selectedSecteur'));

            if ($this->isDirecteur || $this->isAdministratif) {
                if ($this->commercial) {
                    $query->where(function ($q) {
                        $q->where('rv.id_commercial', $this->commercial)
                            ->orWhere('rv.partage_avec', $this->commercial)
                            ->orWhere('v.id_commercial_3', $this->commercial);
                    });
                }

            } elseif ($this->isResponsable) {
                $groupeResponsable = DB::table('groupes')
                    ->where('id_responsable', $currentUserId)
                    ->value('id');

                if ($groupeResponsable) {
                    $query->where(function ($q) use ($groupeResponsable) {
                        $q->whereIn('rv.id_commercial', function ($sub) use ($groupeResponsable) {
                            $sub->select('id')->from('users')->where('groupe', $groupeResponsable)->where('active', 1);
                        })->orWhereIn('rv.partage_avec', function ($sub) use ($groupeResponsable) {
                            $sub->select('id')->from('users')->where('groupe', $groupeResponsable)->where('active', 1);
                        });
                    });

                    if ($this->commercial) {
                        $query->where(function ($q) {
                            $q->where('rv.id_commercial', $this->commercial)
                                ->orWhere('rv.partage_avec', $this->commercial)
                                ->orWhere('v.id_commercial_3', $this->commercial);
                        });
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }

            } else {
                $query->where(function ($q) use ($currentUserId) {
                    $q->where('rv.id_commercial', $currentUserId)
                        ->orWhere('rv.partage_avec', $currentUserId);
                });
            }
        } else {
            $query->where('c.id', $this->selectedClientId);
        }

        $rows = $query->orderBy('rv.date', 'asc')->get();

        $this->argus = $rows->map(function ($argu) {
            return [
                'id' => $argu->id,
                'date' => Carbon::parse($argu->date)->format('d/m/Y'),
                'client' => trim((ucfirst(strtolower($argu->nom ?? ''))).' '.(ucfirst(strtolower($argu->prenom ?? '')))),
                'devis' => $argu->devis_total_ht,
                'tarif' => $argu->tarif_total_ht,
                'resultat' => $argu->resultat,
                'commentaire' => $argu->commentaire,
                'commercial' => CommerciauxService::getCommercialById($argu->commercial_id)->display_name,
                'partage' => $argu->partage_id ? CommerciauxService::getCommercialById($argu->partage_id)->display_name : '',
                'com3' => $argu->com3_id ? CommerciauxService::getCommercialById($argu->com3_id)->display_name : '',
                'statut' => $argu->statut,
            ];
        });
    }

    public function delete($id)
    {
        DB::table('ventes')->where('id_rdv', $id)->delete();
        DB::table('rendez_vous')->where('id', $id)->delete();

        $this->loadData();

        $this->message = 'Argumentation supprimée avec succès ✅';
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

        return view('livewire.argumentations', [
            'years' => $years,
            'months' => $months,
            'argus' => $this->argus,
            'lstCommerciaux' => $this->lstCommerciaux,
        ]);
    }
}
