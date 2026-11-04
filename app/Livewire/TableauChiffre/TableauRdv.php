<?php

namespace App\Livewire\TableauChiffre;

use Livewire\Component;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\TableauChiffreService;

class TableauRdv extends Component
{
    public $month;
    public $year;
    public $groupes = [];
    public $isDirecteur = false;
    public $isTelepro = false;
    public $isAdmin = false;
    public $sortColumn = 'nom';
    public $sortDirection = 'asc';
    public $totauxGlobaux;
    public $totauxSecteur;

    
    protected $listeners = ['filters-updated' => 'applyFilters'];

    public function mount($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->initializeUserData();
        $this->loadGroupes();
    }

    private function initializeUserData()
    {
        $userData = TableauChiffres::getInitializedUserData();
        $this->isAdmin = $userData->isAdmin;
        $this->isDirecteur = $userData->isDirecteur;
        $this->isTelepro = $userData->isTelepro;
    }

    public function applyFilters($data)
    {
        $this->month = $data['month'];
        $this->year = $data['year'];
        $this->loadGroupes();
    }

    public function updatedMonth($value)
    {
        $this->month = $value;
        $this->loadGroupes();
    }

    public function updatedYear($value)
    {
        $this->year = $value;
        $this->loadGroupes();
    }

    public function sortBy($column)
    {
        $this->sortDirection = $this->sortColumn === $column
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortColumn = $column;
        $this->loadGroupes();
    }

    private function getDateRange()
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        return [$start->toDateString(), $end->toDateString()];
    }

    private function isFinPeriodeEssai($dateEntree)
    {
        $date = Carbon::parse($dateEntree)->startOfDay();
        $debut = now()->startOfDay()->subMonths(3);
        $fin = now()->startOfDay()->subMonths(3)->addDays(21);
        return $date->between($debut, $fin);
    }

    private function nombreJoursOuvres()
    {
        [$start, $end] = $this->getDateRange();  // 1er → fin mois
        $today = Carbon::today();
        
        
        // Limite fin à today si mois futur
        $end = $today->gt($end) ? $end : $today->toString();
        return collect(CarbonPeriod::create($start, $end))
            ->filter(fn($date) => !$date->isWeekend())
            ->count();
    }

    public function calculerNombreRdv($userId)
    {
        [$start, $end] = $this->getDateRange();

        return DB::table('clients as c')
            ->join('projets as p', 'p.id_client', '=', 'c.id')
            ->where('c.rentre_par', $userId)
            ->where('p.rdv_annule', 0)
            ->whereBetween('p.date_confirmation', [$start, $end])
            ->count();
    }

    public function nombreRdvSemaine($userId)
    {
        $semaineDefinitions = TableauChiffreService::getSemainesDefinitions($this->year, $this->month);
        $data = [];

        foreach ($semaineDefinitions as [$start, $end]) {
            $data[] = DB::table('clients as c')
                ->join('projets as p', 'p.id_client', '=', 'c.id')
                ->where('c.rentre_par', $userId)
                ->where('p.rdv_annule', 0)
                ->whereBetween('p.date_confirmation', [
                    Carbon::create($this->year, $this->month, $start),
                    Carbon::create($this->year, $this->month, $end)
                ])
                ->count();
        }

        return $data;
    }

    public function rdvJours($userId)
    {
        $today = Carbon::today();
        $data = [];
        $premierJourSemaine = $today->copy()->startOfWeek();

        for ($i = 0; $i < 6; $i++) {
            $date = $premierJourSemaine->copy()->addDays($i);

            if($date->lte($today)){
                $data[] = DB::table('clients as c')
                    ->join('projets as p', 'p.id_client', '=', 'c.id')
                    ->where('c.rentre_par', $userId)
                    ->where('p.rdv_annule', 0)
                    ->whereDate('p.date_confirmation', $date->toDateString())
                    ->count();
            }else{
                $argusParJour[] = '-';
            }
        }

        return $data;
    }

    private function initializeGlobalTotals()
    {
        return (object)[
            'somme_objectif_rdv' => 0,
            'somme_ca_realise' => 0,
            'somme_rdv_total' => 0,
            'somme_argus_total' => 0,
            'somme_rdv_par_semaine' => [0, 0, 0, 0, 0],
            'somme_rdv_par_jours' => [0, 0, 0, 0, 0, 0],
            'somme_rdv_effectues' => 0,
            'total_cc_jour' => 0,
        ];
    }

    private function processGroupes($groupes, $startDate, $endDate, $joursOuvres, $user, &$global, &$globalSecteur)
    {
        foreach ($groupes as $groupe) {
            $query = TableauChiffreService::buildUsersQuery($groupe, $startDate, $endDate, $user, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
            $groupe->users = $query->get();
            $this->calculateGroupeData($groupe, $joursOuvres);
            $this->updateGlobalTotals($global, $groupe, $globalSecteur);
        }

        $this->finalizeGlobalTotals($global, $globalSecteur, $groupes);

        return $groupes;
    }
    
    private function getSortValue($user)
    {
        return match($this->sortColumn) {
            'prenom' => $user->prenom,
            'rdv_total' => $user->rdv_total,
            'rdv_objectif' => $user->objectif_rdv,
            'S1_rdv' => $user->rdv_par_semaine[0] ?? 0,
            'S2_rdv' => $user->rdv_par_semaine[1] ?? 0,
            'S3_rdv' => $user->rdv_par_semaine[2] ?? 0,
            'S4_rdv' => $user->rdv_par_semaine[3] ?? 0,
            'S5_rdv' => $user->rdv_par_semaine[4] ?? 0,
            'S6_rdv' => $user->rdv_par_semaine[5] ?? 0,
            'J1_rdv' => $user->rdv_par_jours[5] ?? 0,
            'J2_rdv' => $user->rdv_par_jours[4] ?? 0,
            'J3_rdv' => $user->rdv_par_jours[3] ?? 0,
            'J4_rdv' => $user->rdv_par_jours[2] ?? 0,
            'J5_rdv' => $user->rdv_par_jours[1] ?? 0,
            'J6_rdv' => $user->rdv_par_jours[0] ?? 0,
            default => $user->prenom,
        };
    }

    private function calculateGroupeData(&$groupe, $joursOuvres)
    {
        $groupe->somme_rdv_effectues = 0;
        $groupe->somme_rdv_par_semaine = [0, 0, 0, 0, 0];
        $groupe->somme_rdv_par_jours = [0, 0, 0, 0, 0, 0];

        foreach ($groupe->users as $user) {
            $user->objectif_rdv = $user->objectif_rdv ?? 0;
            $user->rdv_total = $this->calculerNombreRdv($user->id);
            $user->rdv_par_semaine = $this->nombreRdvSemaine($user->id);
            //dd($user->rdv_par_semaine);
            $user->rdv_par_jours = $this->rdvJours($user->id);
            $user->cc_jour = $joursOuvres > 0 ? ($user->rdv_total / $joursOuvres) : 0;
            $user->is_fin_periode_essai = $this->isFinPeriodeEssai($user->date_entree);

            $groupe->somme_rdv_effectues += $user->cc_jour;

            foreach ($user->rdv_par_semaine as $i => $val) {
                if(!isset($groupe->somme_rdv_par_semaine[$i]))
                    $groupe->somme_rdv_par_semaine[$i] = $val;    
                else
                    $groupe->somme_rdv_par_semaine[$i] += $val;
            }

            foreach ($user->rdv_par_jours as $i => $val) {
                $groupe->somme_rdv_par_jours[$i] += $val;
            }
        }

        /*$groupe->users = $groupe->users->sortBy(function ($user) {
            return $this->getSortValue($user);
        }, SORT_REGULAR, $this->sortDirection === 'desc')->values();*/

        $groupe->somme_objectif_rdv = $groupe->users->sum('objectif_rdv');
        $groupe->somme_rdv_total = $groupe->users->sum('rdv_total');
        $groupe->total_cc_jour = count($groupe->users) > 0 ? ($groupe->somme_rdv_effectues / count($groupe->users)) : 0;
    }

    private function updateGlobalTotals(&$global, $groupe, &$globalSecteur)
    {
        $global->somme_objectif_rdv += $groupe->somme_objectif_rdv;
        $global->somme_rdv_total += $groupe->somme_rdv_total;
        $global->somme_rdv_effectues += $groupe->somme_rdv_effectues;

        foreach ($groupe->somme_rdv_par_semaine as $i => $v) {
            if(!isset($global->somme_rdv_par_semaine[$i]))
                $global->somme_rdv_par_semaine[$i] = $v;
            else
                $global->somme_rdv_par_semaine[$i] += $v;
        }

        foreach ($groupe->somme_rdv_par_jours as $i => $v) {
            $global->somme_rdv_par_jours[$i] += $v;
        }

        if($groupe->id_secteur == session('selectedSecteur')){
            $globalSecteur->somme_objectif_rdv += $groupe->somme_objectif_rdv;
            $globalSecteur->somme_rdv_total += $groupe->somme_rdv_total;
            $globalSecteur->somme_rdv_effectues += $groupe->somme_rdv_effectues;

            foreach ($groupe->somme_rdv_par_semaine as $i => $v) {
                if(!isset($globalSecteur->somme_rdv_par_semaine[$i]))
                    $globalSecteur->somme_rdv_par_semaine[$i] = $v;
                else
                    $globalSecteur->somme_rdv_par_semaine[$i] += $v;
            }

            foreach ($groupe->somme_rdv_par_jours as $i => $v) {
                $globalSecteur->somme_rdv_par_jours[$i] += $v;
            }
        }
    }

    public function finalizeGlobalTotals($global, $globalSecteur, $groupes){

        $countGlobalUsers = 0;
        $countSecteurUsers = 0;
        foreach($groupes as $groupe){
            if($groupe->secteur == session('selectedSecteur'))
                $countSecteurUsers += count($groupe->users);
            $countGlobalUsers += count($groupe->users);
        }

        $global->total_cc_jour = $countGlobalUsers > 0
            ? $global->somme_rdv_effectues / $countGlobalUsers
            : 0;
        
        $globalSecteur->total_cc_jour = $countSecteurUsers > 0
                ? $globalSecteur->somme_rdv_effectues / $countSecteurUsers
                : 0;
    }

    public function loadGroupes()
    {
        [$startDate, $endDate] = $this->getDateRange();
        $joursOuvres = $this->nombreJoursOuvres();
        $user = Auth::user();

        $groupes = TableauChiffres::getGroupesForUser($user, $startDate, $endDate, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
        $global = $this->initializeGlobalTotals();
        $globalSecteur = $this->initializeGlobalTotals();

        $this->groupes = $this->processGroupes($groupes, $startDate, $endDate, $joursOuvres, $user, $global, $globalSecteur);

        foreach($this->groupes as $index => $groupe){
            if($groupe->id_secteur != session('selectedSecteur'))
                unset($this->groupes[$index]);
        }

        $this->totauxGlobaux = $global;
        $this->totauxSecteur = $globalSecteur;
    }
    public function render()
    {
        return view('livewire.tableau-chiffre.tableau-rdv');
    }
}
