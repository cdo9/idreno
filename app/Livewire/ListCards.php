<?php

namespace App\Livewire;

use App\Livewire\Traits\BlocagesTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListCards extends Component
{
    use BlocagesTrait;

    public $alertesParType = [];

    public $alerteTypes = [
        'poses',
        'relances',
        'confirmations-de-rendez-vous',
        'Anniversaires',
        'tableau-chiffres',
        'blocages',
        'alertes',
    ];

    public $isTelepro;

    public $isProspecteur;

    public $isAdmin;

    public $isDirecteur;

    public $blocages;

    public $users;

    public function mount()
    {
        $user = Auth::user();
        $this->isTelepro = $user->hasRole('telepro');
        $this->isProspecteur = $user->hasRole('prospecteur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isDirecteur = $user->hasRole('directeur');

        $this->users = Blocages::loadUsers($this->isDirecteur);

        if (session('alertesParType')) {
            $this->alertesParType = session('alertesParType');
        } else {
            $this->alertesParType = [
                'confirmations-de-rendez-vous' => $this->getAlertConfirmations($user->id),
                'relances' => $this->getAlertRelances($user->id),
                'poses' => $this->getAlertPoses($user),
                'Anniversaires' => $this->getAlertAnniversaire($user->id),
                'tableau-chiffres' => $this->getAlertTableauChiffres(),
                'blocages' => $this->getAlertBlocages(),
                'alertes' => $this->getAlertAlertes()];
            session(['alertesParType' => $this->alertesParType]);
        }
    }

    private function getAlertConfirmations($user)
    {
        $today = Carbon::today();

        $daysToAdd = 2;
        $datePlusTwoBusinessDays = $today->copy();

        while ($daysToAdd > 0) {
            $datePlusTwoBusinessDays->addDay();
            if (! $datePlusTwoBusinessDays->isWeekend()) {
                $daysToAdd--;
            }
        }

        return DB::table('projets as p')
            ->select(
                DB::raw('count(p.id) as cpt')
            )
            ->leftJoin('clients as c', 'c.id', '=', 'p.id_client')
            ->where(function ($q) use ($user) {
                $q->where('c.rentre_par', '=', $user)
                    ->orWhere('c.partage_avec', '=', $user);
            })
            ->where('p.rdv_confirme', '=', 0)
            ->where('p.rdv_annule', '=', 0)
            ->whereDate('p.date', '>=', $today)
            ->whereDate('p.date', '<=', $daysToAdd)
            ->first();
    }

    private function getAlertRelances($user)
    {
        $today = Carbon::today();
        $datePlus30Days = $today->copy()->addDays(30);

        $rdv = DB::table('rendez_vous as rdv')
            ->select(
                DB::raw('count(rdv.id) as cpt')
            )
            ->leftJoin('clients as c', 'c.id', '=', 'rdv.id_client')
            ->where(function ($q) use ($user) {
                $q->where('c.rentre_par', '=', $user)
                    ->orWhere('c.partage_avec', '=', $user);
            })
            ->where('rdv.relance_confirme', '=', 0)
            ->whereDate('rdv.date_relance', '>=', $today)
            ->whereDate('rdv.date_relance', '<=', $datePlus30Days)
            ->first();

        $client = DB::table('clients as c')
            ->select(
                DB::raw('count(c.id) as cpt')
            )
            ->where(function ($q) use ($user) {
                $q->where('c.rentre_par', '=', $user)
                    ->orWhere('c.partage_avec', '=', $user);
            })
            ->whereDate('c.date_relance', '>=', $today)
            ->whereDate('c.date_relance', '<=', $datePlus30Days)
            ->first();

        return (object) [
            'cpt' => $client->cpt + $rdv->cpt,
        ];
    }

    private function getAlertPoses($user)
    {
        $today = Carbon::today();
        $endDate = $today->copy()->subDays(9);
        $poses = DB::table('poses as p')
            ->select(
                DB::raw('count(1) as cpt')
            )
            ->leftJoin('clients as c', 'c.id', '=', 'p.id_client')
            ->where(function ($q) use ($user) {
                $q->where('c.rentre_par', '=', $user->id)
                    ->orWhere('c.partage_avec', '=', $user->id);
            })
            ->whereDate('p.date_debut', '>=', $today)
            ->whereDate('p.date_debut', '<=', $today)
            ->first();

        $alerte = false;
        if ($this->isAdmin) {
            foreach ($this->users as $user) {
                $ind = $this->getIndicateursChantiers($user, $this->isTelepro, $this->isProspecteur, $endDate);
                if ($ind['alerte']) {
                    $alerte = true;
                    break;
                }
            }
        } else {
            $ind = $this->getIndicateursChantiers($user, $this->isTelepro, $this->isProspecteur, $endDate);
            if ($ind['alerte']) {
                $alerte = true;
            }
        }

        return (object) [
            'cpt' => $poses->cpt,
            'alerte' => $alerte,
        ];
    }

    private function getAlertAnniversaire($user)
    {
        $yesterday = Carbon::today()->subDay();
        $tomorrow = Carbon::today()->addDay();

        return DB::table('clients as c')
            ->select(
                DB::raw('count(1) as cpt')
            )
            ->where(function ($q) use ($user) {
                $q->where('c.rentre_par', '=', $user)
                    ->orWhere('c.partage_avec', '=', $user);
            })
            ->whereDate('c.date_anniversaire', '>=', $yesterday)
            ->whereDate('c.date_anniversaire', '<=', $tomorrow)
            ->first();
    }

    private function getAlertTableauChiffres()
    {
        $start = Carbon::today()->subMonths(3);
        $end = $start->copy()->addDays(21);

        return DB::table('users as u')
            ->select(
                DB::raw('count(1) as cpt')
            )
            ->whereDate('u.date_entree', '>=', $start)
            ->whereDate('u.date_entree', '<=', $end)
            ->where('u.active', '=', 1)
            ->first();
    }

    private function getAlertBlocages()
    {
        $cpt = $this->countBlocages($this->users, $this->isTelepro, $this->isProspecteur, $this->isAdmin, $this->isDirecteur);

        return (object) [
            'cpt' => $cpt,
        ];
    }

    private function getAlertAlertes()
    {
        $cpt = DB::table('alerts')
            ->whereNull('deleted_at')
            ->count();

        return (object) [
            'cpt' => $cpt,
        ];
    }

    public function render()
    {
        return view('livewire.list-cards');
    }
}
