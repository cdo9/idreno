<?php

namespace App\Livewire\TableauChiffre;

use App\Models\Secteur;
use App\Services\TableauChiffreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TableauChiffres extends Component
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

    public static function getInitializedUserData()
    {
        $user = Auth::user();
        $result = (object) [
            'isDirecteur' => $user->hasRole('directeur'),
            'isTelepro' => $user->hasRole('telepro'),
            'isAdmin' => $user->hasRole('admin'),

        ];

        return $result;
    }

    public function applyFilters($data)
    {
        $this->month = $data['month'];
        $this->year = $data['year'];
        $this->loadGroupes();
    }

    public function updatedMonth()
    {
        $this->loadGroupes();
    }

    public function updatedYear()
    {
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

    public function rdv48Hours($userId)
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->subDays(2);

        $nbRdv48H = DB::table('clients as c')
            ->join('projets as p', 'p.id_client', '=', 'c.id')
            ->where('c.rentre_par', $userId)
            ->where('p.rdv_annule', 0)
            ->whereBetween('p.date_confirmation', [$endDate, $startDate])
            ->selectRaw('count(p.id) as total')
            ->first();

        return $nbRdv48H?->total ?? 0;
    }

    // === MÉTHODES UTILITAIRES ===

    // === MÉTHODES DE CHARGEMENT PRINCIPALES ===

    public function loadGroupes()
    {
        $user = Auth::user();

        $groupes = TableauChiffres::getGroupesForUser($user, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
        $global = TableauChiffreService::initializeGlobalTotals();
        $globalSecteur = [];
        $secteurs = Secteur::get();
        foreach ($secteurs as $secteur) {
            $globalSecteur[$secteur->id] = TableauChiffreService::initializeGlobalTotals();
        }

        $groupesAvecUsers = TableauChiffreService::processGroupes($groupes, $this->year, $this->month, $user, $global, $globalSecteur, $this->isAdmin, $this->isDirecteur, $this->isTelepro);
        TableauChiffreService::finalizeGlobalTotals($global, $globalSecteur);

        foreach ($groupesAvecUsers as $index => $groupe) {
            if ($groupe->id_secteur != session('selectedSecteur')) {
                unset($groupesAvecUsers[$index]);
            }
        }

        $this->groupes = $groupesAvecUsers;
        $this->totauxGlobaux = $global;
        $this->totauxSecteur = $globalSecteur[session('selectedSecteur')];
    }

    public static function getGroupesForUser($user, $isAdmin, $isDirecteur, $isTelepro)
    {
        if ($isAdmin || $isDirecteur) {
            return DB::table('groupes')
                ->leftJoin('secteurs', 'groupes.secteur', '=', 'secteurs.id')
                ->select('groupes.*', 'secteurs.nom as nom_secteur', 'secteurs.id as id_secteur')
                ->orderBy('tri')->get();
        }

        /*if (DB::table('groupes')->where('id_responsable', $user->id)->exists()) {
            return DB::table('groupes')
                ->leftJoin('secteurs', 'groupes.secteur', '=', 'secteurs.id')
                ->select('groupes.*', 'secteurs.nom as nom_secteur', 'secteurs.id as id_secteur')
                ->where('id_responsable', $user->id)
                ->orderBy('tri')
                ->get();
        }*/

        if ($isTelepro) {
            DB::table('groupes')
                ->leftJoin('secteurs', 'groupes.secteur', '=', 'secteurs.id')
                ->select('groupes.*', 'secteurs.nom as nom_secteur', 'secteurs.id as id_secteur')
                ->where('groupes.id', $user->groupe)
                ->orderBy('tri')
                ->get();

        }

        return DB::table('groupes')
            ->leftJoin('secteurs', 'groupes.secteur', '=', 'secteurs.id')
            ->select('groupes.*', 'secteurs.nom as nom_secteur', 'secteurs.id as id_secteur')
            ->orderBy('tri')
            ->get();
    }

    public function render()
    {
        return view('livewire.tableau-chiffre.tableau-chiffres');
    }
}
