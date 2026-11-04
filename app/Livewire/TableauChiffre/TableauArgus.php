<?php

namespace App\Livewire\TableauChiffre;

use App\Services\TableauChiffreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TableauArgus extends Component
{
    public $month;

    public $year;

    public $groupes = [];

    public $isDirecteur = false;

    public $isTelepro = false;

    public $isAdmin = false;

    public $sortColumn = 'nom';

    public $sortDirection = 'asc';

    public $ventes = [];

    public $projets = [];

    public $rdvs = [];

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
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->loadGroupes();
    }

    // === MÉTHODES DE CALCUL ===

    public function calculerArgusTotal($userId, $groupe)
    {
        [$startDate, $endDate] = $this->getDateRange();

        $result = DB::table('rendez_vous')
            ->where(function ($query) use ($userId) {
                $query->where('id_commercial', $userId)
                    ->orWhere('partage_avec', $userId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->where('secteur', $groupe->secteur)
            ->selectRaw('sum(if(partage_avec is null, 1, 0.5)) as cpt_total, count(id) as cpt')
            ->first();

        return $result;
    }

    public function argusSemaine($userId, $groupe, $semaines)
    {
        $argusParSemaine = [];

        foreach ($semaines as [$startDay, $endDay]) {
            $startDate = Carbon::createFromDate($this->year, $this->month, $startDay)->toDateString();
            $endDate = Carbon::createFromDate($this->year, $this->month, $endDay)->toDateString();

            $argus = DB::table('rendez_vous')
                ->where(function ($query) use ($userId) {
                    $query->where('id_commercial', $userId)
                        ->orWhere('partage_avec', $userId);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->where('secteur', $groupe->secteur)
                ->selectRaw('sum(if(partage_avec is null, 1, 0.5)) as cpt_total, count(id) as cpt')
                ->first() ?? (object) ['cpt' => 0, 'cpt_total' => 0];

            if ($argus->cpt_total == null) {
                $argus->cpt_total = 0;
            }
            $argusParSemaine[] = $argus;
        }

        return $argusParSemaine;
    }

    public function argusJours($userId, $groupe)
    {
        $today = Carbon::today();
        $argusParJour = [];
        $premierJourSemaine = $today->copy()->startOfWeek();

        for ($i = 0; $i < 6; $i++) {
            $date = $premierJourSemaine->copy()->addDays($i);

            if ($date->lte($today)) {
                $argusJour = DB::table('rendez_vous')
                    ->where('id_commercial', $userId)
                    ->whereDate('date', $date->toString())
                    ->where('secteur', $groupe->secteur)
                    ->count();

                $argusParJour[] = $argusJour;
            } else {
                $argusParJour[] = '-';
            }
        }

        return $argusParJour;
    }

    // === MÉTHODES UTILITAIRES ===

    private function getDateRange()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->toDateString();

        return [$startDate, $endDate];
    }

    private function isFinPeriodeEssai($dateEntree)
    {
        $dateEntree = Carbon::parse($dateEntree)->startOfDay();
        $debut = now()->startOfDay()->subMonths(3);
        $fin = now()->startOfDay()->subMonths(3)->addDays(21);

        return $dateEntree->between($debut, $fin);
    }

    private function getSortValue($user)
    {
        switch ($this->sortColumn) {
            case 'prenom':
                return $user->prenom;
            case 'rdv_objectif':
                return $user->objectif_rdv;
            case 'argus_total':
                return $user->argus_total;
            case 'S1_argus':
                return $user->argus_par_semaine[0] ?? 0;
            case 'S2_argus':
                return $user->argus_par_semaine[1] ?? 0;
            case 'S3_argus':
                return $user->argus_par_semaine[2] ?? 0;
            case 'S4_argus':
                return $user->argus_par_semaine[3] ?? 0;
            case 'S5_argus':
                return $user->argus_par_semaine[4] ?? 0;
            case 'S6_argus':
                return $user->argus_par_semaine[5] ?? 0;
            case 'J1_argus':
                return $user->argus_par_jours[5] ?? 0;
            case 'J2_argus':
                return $user->argus_par_jours[4] ?? 0;
            case 'J3_argus':
                return $user->argus_par_jours[3] ?? 0;
            case 'J4_argus':
                return $user->argus_par_jours[2] ?? 0;
            case 'J5_argus':
                return $user->argus_par_jours[1] ?? 0;
            case 'J6_argus':
                return $user->argus_par_jours[0] ?? 0;
            default:
                return $user->prenom;
        }
    }

    // === MÉTHODES DE CHARGEMENT PRINCIPALES ===

    public function loadGroupes()
    {
        [$startDate, $endDate] = $this->getDateRange();
        $user = Auth::user();

        $groupes = TableauChiffres::getGroupesForUser($user, $startDate, $endDate, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
        $global = $this->initializeGlobalTotals();
        $globalSecteur = $this->initializeGlobalTotals();

        $groupesAvecUsers = $this->processGroupes($groupes, $startDate, $endDate, $user, $global, $globalSecteur);

        foreach ($groupesAvecUsers as $index => $groupe) {
            if ($groupe->id_secteur != session('selectedSecteur')) {
                unset($groupesAvecUsers[$index]);
            }
        }

        $this->groupes = $groupesAvecUsers;
        $this->totauxGlobaux = $global;
        $this->totauxSecteur = $globalSecteur;
    }

    private function initializeGlobalTotals()
    {
        return (object) [
            'somme_objectif_rdv' => 0,
            'somme_argus_total' => 0,
            'somme_argus_par_semaine' => [0, 0, 0, 0, 0],
            'somme_argus_par_jours' => [0, 0, 0, 0, 0, 0],
        ];
    }

    private function processGroupes($groupes, $startDate, $endDate, $user, $global, $globalSecteur)
    {
        $groupesAvecUsers = collect();

        foreach ($groupes as $groupe) {
            $this->loadGroupeUsers($groupe, $startDate, $endDate, $user);
            $this->calculateGroupeData($groupe);
            $this->updateGlobalTotals($global, $groupe, $globalSecteur);
            $groupesAvecUsers->push($groupe);
        }
        // dd($groupesAvecUsers);

        return $groupesAvecUsers;
    }

    private function loadGroupeUsers($groupe, $startDate, $endDate, $user)
    {
        $usersQuery = TableauChiffreService::buildUsersQuery($groupe, $startDate, $endDate, $user, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
        $groupe->users = $usersQuery->get();
        $this->initializeGroupeSums($groupe);
    }

    private function initializeGroupeSums($groupe)
    {
        $groupe->somme_rdv_effectues = 0;
    }

    private function calculateGroupeData($groupe)
    {
        foreach ($groupe->users as $user) {
            $this->calculateUserData($user, $groupe);
        }

        // $this->sortUsers($groupe);
        $this->calculateGroupeSums($groupe);
    }

    private function calculateUserData($user, $groupe)
    {
        $user->objectif_rdv = $user->objectif_rdv ?? 0;
        $argusTotal = $this->calculerArgusTotal($user->id, $groupe);
        $user->argus_count = $argusTotal->cpt;
        $user->argus_total = $argusTotal->cpt_total;
        $semaines = TableauChiffreService::getSemainesDefinitions($this->year, $this->month);
        $user->argus_par_semaine = $this->argusSemaine($user->id, $groupe, $semaines);
        $user->argus_par_jours = $this->argusJours($user->id, $groupe);
        $user->is_fin_periode_essai = $this->isFinPeriodeEssai($user->date_entree);
    }

    private function sortUsers($groupe)
    {
        $groupe->users = $groupe->users->sortBy(function ($user) {
            return $this->getSortValue($user);
        }, SORT_REGULAR, $this->sortDirection === 'desc')->values();
    }

    private function calculateGroupeSums($groupe)
    {
        $groupe->somme_objectif_rdv = $groupe->users->sum('objectif_rdv');
        $groupe->somme_argus_total = $groupe->users->sum('argus_total');
        $groupe->somme_argus_par_jours = [0, 0, 0, 0, 0, 0];
        $groupe->somme_argus_par_semaine = [0, 0, 0, 0, 0];

        foreach ($groupe->users as $user) {
            foreach ($user->argus_par_semaine as $index => $argus) {
                if (! isset($groupe->somme_argus_par_semaine[$index])) {
                    $groupe->somme_argus_par_semaine[$index] = $argus->cpt_total;
                } else {
                    $groupe->somme_argus_par_semaine[$index] += $argus->cpt_total;
                }
            }
            foreach ($user->argus_par_jours as $index => $argusJour) {
                if ($argusJour != '-') {
                    $groupe->somme_argus_par_jours[$index] += $argusJour;
                }
            }
        }
    }

    private function updateGlobalTotals($global, $groupe, $globalSecteur)
    {
        $global->somme_objectif_rdv += $groupe->somme_objectif_rdv;
        $global->somme_argus_total += $groupe->somme_argus_total;

        foreach ($groupe->somme_argus_par_semaine as $i => $v) {
            if (! isset($global->somme_argus_par_semaine[$i])) {
                $global->somme_argus_par_semaine[$i] = $v;
            } else {
                $global->somme_argus_par_semaine[$i] += $v;
            }
        }
        foreach ($groupe->somme_argus_par_jours as $i => $v) {
            if ($v != '-') {
                $global->somme_argus_par_jours[$i] += $v;
            }
        }

        if ($groupe->id_secteur == session('selectedSecteur')) {
            $globalSecteur->somme_objectif_rdv += $groupe->somme_objectif_rdv;
            $globalSecteur->somme_argus_total += $groupe->somme_argus_total;

            foreach ($groupe->somme_argus_par_semaine as $i => $v) {
                if (! isset($globalSecteur->somme_argus_par_semaine[$i])) {
                    $globalSecteur->somme_argus_par_semaine[$i] = $v;
                } else {
                    $globalSecteur->somme_argus_par_semaine[$i] += $v;
                }
            }
            foreach ($groupe->somme_argus_par_jours as $i => $v) {
                if ($v != '-') {
                    $globalSecteur->somme_argus_par_jours[$i] += $v;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.tableau-chiffre.tableau-argus');
    }
}
