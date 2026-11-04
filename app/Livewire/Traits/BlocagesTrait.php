<?php

namespace App\Livewire\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait BlocagesTrait
{
    protected int $cacheTTL = 600;

    private function getComparaisonDate()
    {
        $now = Carbon::now('Europe/Paris');  // Fuseau France

        if ($now->hour < 9 || ($now->hour == 9 && $now->minute < 30)) {
            $date = $now->copy()->subDay()->format('Y-m-d');  // Veille
        } else {
            $date = $now->format('Y-m-d');  // Aujourd'hui
        }

        return $date;
    }

    public function blocageGetEcarts($user, $isTelepro, $isProspecteur)
    {
        if ($isTelepro || $isProspecteur) {
            return [];
        }

        $cacheKey = "blocage_ecarts_user_{$user->id}";

        $date = $this->getComparaisonDate();

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $date) {
            return DB::table('projets as p')
                ->select(
                    'p.date',
                    DB::raw('count(distinct p.id) as cpt_rdv'),
                    DB::raw('count(distinct rv.id) as cpt_argu')
                )
                ->leftJoin('clients as c', 'c.id', '=', 'p.id_client')
                ->leftJoin('rendez_vous as rv', function ($join) {
                    $join->on('p.id_client', '=', 'rv.id_client')
                        ->on('p.date', '=', 'rv.date');
                })
                ->where(function ($q) use ($user) {
                    $q->where('c.rentre_par', $user->id)
                        ->orWhere('c.partage_avec', $user->id);
                })
                ->where('p.rdv_confirme', 1)
                ->where('p.rdv_annule', 0)
                ->whereDate('p.date', '<', $date)
                ->where('p.date', '>=', '2026-04-01')
                ->groupBy('p.date')
                ->havingRaw('cpt_rdv != cpt_argu')
                ->get();
        });
    }

    public function blocageGetEcartsRdv($user, $date)
    {

        $cacheKey = "blocageGetEcartsRdv_{$user->id}_{$date}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $date) {
            return DB::table('projets as p')
                ->select(
                    'p.id as projet_id',
                    'c.*'
                )
                ->leftJoin('clients as c', 'c.id', '=', 'p.id_client')
                ->where(function ($q) use ($user) {
                    $q->where('c.rentre_par', $user->id)
                        ->orWhere('c.partage_avec', $user->id);
                })
                ->where('p.rdv_confirme', '=', 1)
                ->where('p.rdv_annule', '=', 0)
                ->whereDate('p.date', '=', $date)
                ->get();
        });
    }

    public function blocageGetEcartsArgus($user, $date)
    {

        $cacheKey = "blocageGetEcartsArgus_{$user->id}_{$date}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $date) {
            return DB::table('rendez_vous as r')
                ->select('r.id as rdv_id', 'c.*')
                ->leftJoin('clients as c', 'c.id', '=', 'r.id_client')
                ->where(function ($q) use ($user) {
                    $q->where('c.rentre_par', $user->id)
                        ->orWhere('c.partage_avec', $user->id);
                })
                ->whereDate('r.date', '=', $date)
                ->get();
        });
    }

    public function blocageGetEcartsEntretiens($user, $isDirecteur, $isAdmin)
    {

        if (! $isDirecteur || $isAdmin) {
            return [];
        }

        $cacheKey = "blocageGetEcartsEntretiens_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () {
            return DB::table('users')
                ->where('active', 1)
                ->get();
        });

        $ecarts = [];

        foreach ($users as $u) {
            if ($isDirecteur) {
                continue;
            }
            if ($this->isDateEntretienDepasse((array) $u)) {
                $ecarts[] = $u;
            }
        }

        return $ecarts;
    }

    public function blocageGetEvaluations($user, $isTelepro, $isProspecteur)
    {
        if ($user->nouveau_vendeur) {
            return [];
        }

        if ($isTelepro || $isProspecteur) {
            return [];
        }

        $cacheKey = "blocageGetEvaluations_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user) {
            return DB::table('rendez_vous as r')
                ->select(
                    'r.*',
                    'c.*',
                    'u1.nouveau_vendeur as nv1',
                    'u2.nouveau_vendeur as nv2'
                )
                ->leftJoin('clients as c', 'c.id', '=', 'r.id_client')
                ->leftJoin('evaluations as e', 'e.id_argu', '=', 'r.id')
                ->leftJoin('users as u1', function ($join) {
                    $join->on('u1.id', '=', 'r.id_commercial');
                })
                ->leftJoin('users as u2', function ($join) {
                    $join->on('u2.id', '=', 'r.partage_avec');
                })
                ->whereNull('e.id')
                ->where(function ($q) use ($user) {
                    $q->where(function ($r) use ($user) {
                        $r->where('r.id_commercial', '=', $user->id)
                            ->where('u2.nouveau_vendeur', '=', 1);
                    })
                        ->orWhere(function ($r) use ($user) {
                            $r->where('r.partage_avec', '=', $user->id)
                                ->where('u1.nouveau_vendeur', '=', 1);
                        });
                })
                ->whereBetween('r.date', ['2024-09-16', Carbon::yesterday()->format('Y-m-d')])
                ->get();
        });

    }

    public function blocageGetAConfirmer($userId, $isTelepro, $isProspecteur)
    {
        if ($isTelepro || $isProspecteur) {
            return [];
        }

        $cacheKey = "blocageGetAConfirmer_{$userId->id}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($userId) {
            return DB::table('projets as p')
                ->select('p.*', 'c.*')
                ->leftJoin('clients as c', 'c.id', '=', 'p.id_client')
                ->where(function ($q) use ($userId) {
                    $q->where('c.rentre_par', $userId->id)
                        ->orWhere('c.partage_avec', $userId->id);
                })
                ->where('p.rdv_confirme', 0)
                ->where('p.rdv_annule', 0)
                ->where('p.date', '<', DB::raw('CURDATE()'))
                ->get();
        });
    }

    public function blocageGetProspection($user, $isTelepro, $isProspecteur)
    {
        if ($isProspecteur || $isTelepro) {
            return [];
        }

        $cacheKey = "blocageGetProspection_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user) {
            return DB::table('prospections as p')
                ->select('p.*', 'pc.nom as commune')
                ->leftJoin('prospection_communes as pc', 'pc.id', '=', 'p.commune')
                ->where('p.id_commercial', $user->id)
                ->whereNull('p.termine')
                ->where('p.date', '<', DB::raw('CURDATE()'))
                ->get();
        });
    }

    public function hasBlocageQuelconque($user, $isTelepro, $isProspecteur, $isAdmin, $isDirecteur): bool
    {
        if ($user->role == 'admin') {
            return false;
        }

        $ecarts = $this->blocageGetEcarts($user, $isTelepro, $isProspecteur);
        // $aconfirmer   = $this->blocageGetAConfirmer($user, $isTelepro, $isProspecteur);
        $prospections = []; // $this->blocageGetProspection($user, $isTelepro, $isProspecteur);
        $entretiens = []; // $this->blocageGetEcartsEntretiens($user, $isDirecteur, $isAdmin);
        $ind = $this->getIndicateursChantiers($user, $isTelepro, $isProspecteur);
        $blocages = []; // $this->blocageGetEvaluations($user, $isTelepro, $isProspecteur);

        return
            count($ecarts) > 0 ||
            // count($aconfirmer) > 0 ||
            count($prospections) > 0 ||
            count($entretiens) > 0 ||
            ($ind['alerte'] ?? false) ||
            count($blocages) > 0;
    }

    public function countBlocages($users, $isTelepro, $isProspecteur, $isAdmin, $isDirecteur): int
    {
        $cpt = 0;
        foreach ($users as $user) {
            if ($this->hasBlocageQuelconque($user, $isTelepro, $isProspecteur, $isAdmin, $isDirecteur)) {
                $cpt++;
            }
        }

        return $cpt;
    }

    public function isDateEntretienDepasse(array $user): bool
    {
        $dateEntree = Carbon::parse($user['date_entree']);
        $dateEntretien = isset($user['date_entretien'])
            ? Carbon::parse($user['date_entretien'])
            : $dateEntree;

        $now = Carbon::now();

        if ($now->year <= $dateEntretien->year) {
            return false;
        }

        $dateAnniversaire = $dateEntree->copy()->year($now->year)->addMonth();

        return $now->greaterThan($dateAnniversaire);
    }

    public function getIndicateursChantiers($user, $isTelepro, $isProspecteur, $end = null)
    {
        if ($isTelepro || $isProspecteur) {
            return [
                'alerte' => false,
                'alerte_visite' => false,
                'alerte_avis' => false,
                'alerte_facebook' => false,
            ];
        }

        $start = Carbon::parse('2023-12-01')->toDateString();
        $end ??= Carbon::now()->subDays(15)->toDateString();

        $cacheKey = "getIndicateursChantiers_{$user->id}";
        $res = [];
        $res = Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $start, $end) {
            return DB::table('poses as p')
                ->leftJoin('ventes as v', function ($join) {
                    $join->on('v.id', '=', 'p.id_vente');
                })
                ->where('p.indicateur', 1)
                ->whereBetween('p.date_fin', [$start, $end])
                ->where(function ($q) use ($user) {
                    $q->where('v.id_commercial', $user->id)
                        ->orWhere('v.id_commercial_2', $user->id)
                        ->orWhere('v.id_commercial_3', $user->id)
                        ->orWhere('p.id_commercial_visite', $user->id);
                })
                ->select(
                    DB::raw('COUNT(DISTINCT p.id) as chantiers'),
                    DB::raw('FLOOR(SUM(p.visite_chantier)/COUNT(DISTINCT p.id)*100) as ratio_visites'),
                    DB::raw('FLOOR(SUM(p.avis_client)/COUNT(DISTINCT p.id)*100) as ratio_avis'),
                    DB::raw('FLOOR(SUM(p.avis_facebook)/COUNT(DISTINCT p.id)*100) as ratio_facebook')
                )
                ->first();
        });

        $res = (array) $res;

        $res['alerte'] = $res['chantiers'] > 0 && (
            $res['ratio_visites'] < 100 ||
            $res['ratio_avis'] < 100 ||
            $res['ratio_facebook'] < 100
        );

        $res['alerte_visite'] = $res['chantiers'] > 0 && $res['ratio_visites'] < 100;
        $res['alerte_avis'] = $res['chantiers'] > 0 && $res['ratio_avis'] < 100;
        $res['alerte_facebook'] = $res['chantiers'] > 0 && $res['ratio_facebook'] < 100;
        $res['details'] = $this->getIndicateursChantiersDetail($user, $end, $start);

        return $res;
    }

    private function getIndicateursChantiersDetail($user, $end = null, $start = null)
    {
        $cacheKey = "getIndicateursChantiersDetail_{$user->id}";
        $res = [];
        $res = Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $start, $end, &$res) {
            return DB::table('poses as p')
                ->leftJoin('ventes as v', function ($join) {
                    $join->on('v.id', '=', 'p.id_vente');
                })
                ->leftJoin('clients as c', function ($join) {
                    $join->on('c.id', '=', 'p.id_client');
                })
                ->where('p.indicateur', 1)
                ->where(function ($q) use ($user) {
                    $q->where('v.id_commercial', $user->id)
                        ->orWhere('v.id_commercial_2', $user->id)
                        ->orWhere('v.id_commercial_3', $user->id)
                        ->orWhere('p.id_commercial_visite', $user->id);
                })
                ->whereBetween('p.date_fin', [$start, $end])
                ->select('p.visite_chantier', 'p.avis_client', 'p.avis_facebook', 'p.id', 'c.*')
                ->get();
        });

        return $res;
    }
}
