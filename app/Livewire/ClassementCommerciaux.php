<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\User;
use App\Services\TableauChiffreService;

class ClassementCommerciaux extends Component
{
    public $month;
    public $months = [];
    public $year;
    public $years = [];
    public $groupes = [];
    public $users = [];
    public $sortColumn = 'nom';
    public $sortDirection = 'asc';
    public $ventes = [];
    public $projets = [];
    public $isDirecteur = false;
    public $isTelepro = false;
    public $isAdmin = false;
    public $isActive = false;
    public $rdvs = [];
    public $totauxGlobaux;
    public $filtreVue = 'commerciaux';
    
    public function mount(){
        $user = Auth::user();
        if ($user -> active === '1') {
            $this->isActive = true;
        }
        $this->isDirecteur = $user->hasRole('directeur');
        $this->isTelepro = $user->hasRole('telepro');
        $this->isAdmin = $user->hasRole('admin');
        $this->month = '0';
        $this->year = Carbon::now()->year;
        $this->loadRanking();
        $this->months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
        $this->years = range(2014, Carbon::now()->year);
        $this->users = collect($this->users)
            ->sortByDesc('ca_annuel')
            ->values()
            ->all();

        $this->groupes = collect($this->groupes)
            ->sortByDesc('ca_annuel')
            ->values()
            ->all();


    }
    public function applyFilters()
    {
        $this->loadRanking();
    }
    public function updatedMonth()
    {
        $this->loadRanking();
    }

    public function updatedYear()
    {
        $this->loadRanking();
    }
    public function sortiIcon($column)
    {
        if ($this->sortColumn === $column) {
            return $this->sortDirection === 'asc'
                ? '<span class="fa fa-sort-up"></span>'
                : '<span class="fa fa-sort-down"></span>';
        }
        return '<span class="fa fa-sort text-muted"></span>';
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->loadRanking();
    }
    private function getSortValue($user)
    {
        switch ($this->sortColumn) {
            case 'commerciaux':
                return $user->prenom;
            case 'ventesClients':
                return $user->ratio;
            case 'objectif_ca':
                return $user->objectif_ca;
            case 'S1_ca':
                return $user->caParSemaine[1] ?? 0;
            case 'S2_ca':
                return $user->caParSemaine[2] ?? 0;
            case 'S3_ca':
                return $user->caParSemaine[3] ?? 0;
            case 'S4_ca':
                return $user->caParSemaine[4] ?? 0;
            case 'S5_ca':
                return $user->caParSemaine[5] ?? 0;
            case 'M1':
                return $user->ca_month[1] ?? 0;
            case 'M2':
                return $user->ca_month[2] ?? 0;
            case 'M3':
                return $user->ca_month[3] ?? 0;
            case 'M4':
                return $user->ca_month[4] ?? 0;
            case 'M5':
                return $user->ca_month[5] ?? 0;
            case 'M6':
                return $user->ca_month[6] ?? 0;
            case 'M7':
                return $user->ca_month[7] ?? 0;
            case 'M8':
                return $user->ca_month[8] ?? 0;
            case 'M9':
                return $user->ca_month[9] ?? 0;
            case 'M10':
                return $user->ca_month[10] ?? 0;
            case 'M11':
                return $user->ca_month[11] ?? 0;
            case 'M12':
                return $user->ca_month[12] ?? 0;
            case 'objectif_annuel':
                return $user->objectif_annuel;
            case 'ca_annuel':
                return $user->ca_annuel;
            default:
                return $user->prenom;
        }
    }
    private function getGroupSortValue($groupe)
    {
        switch ($this->sortColumn) {
            case 'objectif_ca':
                return $groupe->objectif_ca;
            case 'S1_ca':
                return $groupe->caParSemaine[1] ?? 0;
            case 'S2_ca':
                return $groupe->caParSemaine[2] ?? 0;
            case 'S3_ca':
                return $groupe->caParSemaine[3] ?? 0;
            case 'S4_ca':
                return $groupe->caParSemaine[4] ?? 0;
            case 'S5_ca':
                return $groupe->caParSemaine[5] ?? 0;
            case 'M1':
                return $groupe->ca_month[1] ?? 0;
            case 'M2':
                return $groupe->ca_month[2] ?? 0;
            case 'M3':
                return $groupe->ca_month[3] ?? 0;
            case 'M4':
                return $groupe->ca_month[4] ?? 0;
            case 'M5':
                return $groupe->ca_month[5] ?? 0;
            case 'M6':
                return $groupe->ca_month[6] ?? 0;
            case 'M7':
                return $groupe->ca_month[7] ?? 0;
            case 'M8':
                return $groupe->ca_month[8] ?? 0;
            case 'M9':
                return $groupe->ca_month[9] ?? 0;
            case 'M10':
                return $groupe->ca_month[10] ?? 0;
            case 'M11':
                return $groupe->ca_month[11] ?? 0;
            case 'M12':
                return $groupe->ca_month[12] ?? 0;
            case 'objectif_annuel':
                return $groupe->objectif_annuel;
            case 'ca_annuel':
                return $groupe->ca_annuel;
            default:
                return $groupe->nom;
        }
    }
    private function sortUsers()
    {
        $this->users = $this->users
            ->sortBy(function ($user) {
                return $this->getSortValue($user);
            }, SORT_REGULAR, $this->sortDirection === 'desc')
            ->values();
    }

    private function getVentesSum($userId, $typeVente, $startDate, $endDate)
    {
        return DB::table('ventes')
            ->where('type_vente', $typeVente)
            ->where('id_commercial', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('statut', '!=', 3)
            ->sum('ca_ht');
    }
	
    public function CASemaine($userId)
    {
        $semaines = TableauChiffreService::getSemainesDefinitions($this->year, $this->month);
        $CAParSemaine = [];

        foreach ($semaines as [$startDay, $endDay]) {
            $startDate = Carbon::createFromDate($this->year, $this->month, $startDay)->toDateString();
            $endDate = Carbon::createFromDate($this->year, $this->month, $endDay)->toDateString();
			

            $ca = DB::table('ventes')
                ->where('id_commercial', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('statut', '!=', 3)
                ->sum('ca_ht');

            $CAParSemaine[] = $ca;
        }
        return $CAParSemaine;
    }

    public function calculerCaParMois($userId)
    {
        $CAParMois = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($this->year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($this->year, $month, 1)->endOfMonth()->toDateString();

            $commune = $this->getVentesSum($userId, 'Commune', $startDate, $endDate);
            $normal = $this->getVentesSum($userId, 'Normale', $startDate, $endDate);

            $CAParMois[$month] = $commune + $normal;
        }

        return $CAParMois;
    }

    public function calculerCaAnnuel($userId)
    {
        $year = $this->year;

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay()->toDateString();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay()->toDateString();

        $commune = $this->getVentesSum($userId, 'Commune', $startDate, $endDate);
        $normal = $this->getVentesSum($userId, 'Normale', $startDate, $endDate);

        return $commune + $normal;
    }
    public function calculerRatioVentesUniques($userId)
    {
        $year = $this->year;
        $ventes = DB::table('ventes as v')
            ->select('v.id', DB::raw('COUNT(DISTINCT v2.id_rdv) as cpt'))
            ->leftJoin('clients as c', 'v.id_client', '=', 'c.id')
            ->leftJoin('ventes as v2', function ($join) {
                $join->on('v2.id_client', '=', 'c.id')
                    ->where('v2.statut', '!=', 3);
            })
            ->whereYear('v.date', $year)
            ->where('v.id_commercial', $userId)
            ->where('v.statut', '!=', 3)
            ->groupBy('v.id')
            ->get();

        $total = $ventes->count();

        return $total > 0
            ? $ventes->where('cpt', 1)->count() / $total
            : 0;
    }
    public function getTotalRowProperty()
    {
        return [
            'objectif_ca' => collect($this->users)->sum('objectif_ca'),
            'objectif_annuel' => collect($this->users)->sum('objectif_annuel'),
            'ca_annuel' => collect($this->users)->sum('ca_annuel'),
            'ca_month' => collect(range(1, 12))->mapWithKeys(function ($i) {
                return [$i => collect($this->users)->sum(fn($u) => $u->ca_month[$i] ?? 0)];
            }),
            'caParSemaine' => collect(range(0, 4))->mapWithKeys(function ($i) {
                return [$i => collect($this->users)->sum(fn($u) => $u->caParSemaine[$i] ?? 0)];
            }),
        ];
    }
    private function getObjectifAnnuelGroupe($users)
    {
        $total = 0;

        foreach ($users as $user) {
            $objectif = DB::table('objectif_annuel_commercial')
                ->where('id_user', $user->id)
                ->where('year', $this->year)
                ->value('value');

            $total += $objectif ?? 0;
        }

        return $total;
    }

    private function getCaAnnuelGroupe($users)
    {
        $total = 0;

        foreach ($users as $user) {
            $total += $this->calculerCaAnnuel($user->id);
        }

        return $total;
    }

    private function getRatioGroupe($users)
    {
        $ratios = [];

        foreach ($users as $user) {
            $ratios[] = $this->calculerRatioVentesUniques($user->id);
        }

        return count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0;
    }

    private function getVentesGroupe($users, $startDate, $endDate)
    {
        $total = 0;

        foreach ($users as $user) {
            $total += DB::table('ventes')
                ->where('id_commercial', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('statut', '!=', 3)
                ->count();
        }

        return $total;
    }


    private function getCaParSemaineGroupe($users)
    {
        $semaines = TableauChiffreService::getSemainesDefinitions($this->year, $this->month);
        $caParSemaine = [];

        foreach ($semaines as $index => [$startDay, $endDay]) {
            $total = 0;

            foreach ($users as $user) {
                $startDate = Carbon::createFromDate($this->year, $this->month, $startDay)->toDateString();
                $endDate = Carbon::createFromDate($this->year, $this->month, $endDay)->toDateString();

                $total += DB::table('ventes')
                    ->where('id_commercial', $user->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('statut', '!=', 3)
                    ->sum('ca_ht');
            }

            $caParSemaine[$index + 1] = $total;
        }

        return $caParSemaine;
    }

    private function getCaParMoisGroupe($users)
    {
        $caParMois = [];

        for ($month = 1; $month <= 12; $month++) {
            $total = 0;

            foreach ($users as $user) {
                $startDate = Carbon::createFromDate($this->year, $month, 1)->startOfMonth()->toDateString();
                $endDate = Carbon::createFromDate($this->year, $month, 1)->endOfMonth()->toDateString();

                $commune = $this->getVentesSum($user->id, 'Commune', $startDate, $endDate);
                $normal = $this->getVentesSum($user->id, 'Normale', $startDate, $endDate);

                $total += $commune + $normal;
            }

            $caParMois[$month] = $total;
        }

        return $caParMois;
    }


    private function calculateGroupIndicators($groupe, $startDate, $endDate)
    {
        $users = collect(User::select('users.*')
            ->where('groupe', $groupe->id)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('delete')->orWhereDate('delete', '>=', $startDate);
            })
            ->get());
        $groupe->users = $users;

        $groupe->objectif_ca = $users->sum('objectif_ca');
        $groupe->objectif_annuel = $this->getObjectifAnnuelGroupe($users);
        $groupe->ca_annuel = $this->getCaAnnuelGroupe($users);
        $groupe->ca_month = $this->getCaParMoisGroupe($users);
        $groupe->caParSemaine = $this->getCaParSemaineGroupe($users);
        $groupe->somme_ventes = $this->getVentesGroupe($users, $startDate, $endDate);
        $groupe->ratio = $this->getRatioGroupe($users);

        return $groupe;
    }
    private function calculerClassementCaAnnuel()
    {
        $caAnnuelUsers = collect($this->users)->map(function ($user) {
            return [
                'id' => $user->id,
                'ca_annuel' => $user->ca_annuel
            ];
        })->sortByDesc('ca_annuel')->values();

        $classement = [];
        $rangActuel = 1;
        $casPrecedent = null;
        $compteurUtilisateurs = 0;

        foreach ($caAnnuelUsers as $index => $user) {
            $compteurUtilisateurs++;

            if ($casPrecedent !== null && $user['ca_annuel'] !== $casPrecedent) {
                $rangActuel = $compteurUtilisateurs;
            }

            $classement[$user['id']] = $rangActuel;
            $casPrecedent = $user['ca_annuel'];
        }

        return $classement;
    }
    private function calculerClassementCaAnnuelGroupes()
    {
        $caAnnuelGroupes = collect($this->groupes)->map(function ($groupe) {
            return [
                'id' => $groupe->id,
                'ca_annuel' => $groupe->ca_annuel
            ];
        })->sortByDesc('ca_annuel')->values();

        $classement = [];
        $rangActuel = 1;
        $casPrecedent = null;
        $compteurGroupes = 0;

        foreach ($caAnnuelGroupes as $index => $groupe) {
            $compteurGroupes++;

            if ($casPrecedent !== null && $groupe['ca_annuel'] !== $casPrecedent) {
                $rangActuel = $compteurGroupes;
            }

            $classement[$groupe['id']] = $rangActuel;
            $casPrecedent = $groupe['ca_annuel'];
        }

        return $classement;
    }
    public function loadGroupe($startDate, $endDate)
    {
        $groupes = DB::table('groupes')->orderBy('tri')->get();

        $groupesAvecData = $groupes
            ->map(function ($groupe) use ($startDate, $endDate) {
                return $this->calculateGroupIndicators($groupe, $startDate, $endDate);
            })
            ->filter(fn($g) => $g->ca_annuel > 0)
            ->values();

        $this->groupes = $this->sortDirection === 'desc'
            ? $groupesAvecData->sortByDesc(fn($g) => $this->getGroupSortValue($g))->values()
            : $groupesAvecData->sortBy(fn($g) => $this->getGroupSortValue($g))->values();

        $classementCaGroupes = $this->calculerClassementCaAnnuelGroupes();

        $this->groupes = $this->groupes->map(function ($groupe) use ($classementCaGroupes) {
            $groupe->rang_ca_annuel = $classementCaGroupes[$groupe->id] ?? 0;
            return $groupe;
        });

        $this->totalGroupes = [
            'objectif_ca' => $groupesAvecData->sum('objectif_ca'),
            'objectif_annuel' => $groupesAvecData->sum('objectif_annuel'),
            'ca_annuel' => $groupesAvecData->sum('ca_annuel'),
            'ca_month' => collect(range(1, 12))->mapWithKeys(function ($i) use ($groupesAvecData) {
                return [$i => $groupesAvecData->sum(fn($g) => $g->ca_month[$i] ?? 0)];
            }),
            'caParSemaine' => collect(range(1, 5))->mapWithKeys(function ($i) use ($groupesAvecData) {
                return [$i => $groupesAvecData->sum(fn($g) => $g->caParSemaine[$i] ?? 0)];
            }),
        ];
    }



    private function loadCommerciaux($startDate, $endDate)
    {
        $users = User::select('users.*')
            ->join('ventes', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'ventes.id_commercial')
                    ->whereBetween('ventes.date', [$startDate, $endDate])
                    ->where('ventes.statut', '!=', 3);
            });
            
        if(!$this->isAdmin && !$this->isDirecteur){
            $users->where("active", 1);
        }
        $users = $users->distinct()
        ->get()
        ->map(function ($user) {
            $user->objectif_ca = $user->objectif_ca ?? 0;
            $user->ca_month = $this->calculerCaParMois($user->id);
            $user->ca_annuel = $this->calculerCaAnnuel($user->id);
            $user->caParSemaine = $this->CASemaine($user->id);
            $user->ratio = $this->calculerRatioVentesUniques($user->id);

            $user->objectif_annuel = DB::table('objectif_annuel_commercial')
                ->where('id_user', $user->id)
                ->where('year', $this->year)
                ->value('value') ?? 0;

            return $user;
        });

        $this->users = $users;

        $classementCa = $this->calculerClassementCaAnnuel();

        $this->users = $this->users->map(function ($user) use ($classementCa) {
            $user->rang_ca_annuel = $classementCa[$user->id] ?? 0;
            return $user;
        });

        $this->sortUsers();
    }

    public function updatedFiltreVue()
    {
        // Reset sorting to CA total (descending) when switching filters
        // to ensure proper ranking based on total sales volume
        $this->sortColumn = 'ca_annuel';
        $this->sortDirection = 'desc';

        $this->loadRanking();
    }

    public function loadRanking()
    {
        $year = $this->year;
        $month = $this->month;

        $startDate = $month
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString()
            : Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();

        $endDate = $month
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString()
            : Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();

        if ($this->filtreVue === 'agence') {
            $this->loadGroupe($startDate, $endDate);
            $this->users = []; // vide les commerciaux
        } else {
            $this->loadCommerciaux($startDate, $endDate);
            $this->groupes = []; // vide les groupes
        }
    }




    public function render()
    {

        return view('livewire.classement-commerciaux');
    }
}
