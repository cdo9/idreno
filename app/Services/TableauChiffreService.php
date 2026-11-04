<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TableauChiffreService
{
    public static function getDateRange($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
        return [$startDate, $endDate];
    }

    public static function getSemainesDefinitions($year, $month)
    {
        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $weeks = [];

        // On commence sur le premier lundi trouvé (ou le 1er si le mois commence plus tard)
        $current = $firstDay->copy();
        if ($current->dayOfWeek !== Carbon::MONDAY) {
            $current->next(Carbon::MONDAY);
            // Si le lundi est dans le mois, sinon on commence au 1er jour
            if ($current->month !== $month) {
                $current = $firstDay->copy();
            }
        }

        // Boucle tant que le début de la semaine est dans le mois
        while ($current <= $lastDay) {
            $start = $current->copy();
            // Pour la première semaine, on prend le plus tôt des deux : 1er du mois ou lundi
            if ($weeks === []) {
                $start = $firstDay->copy();
            }
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
            // Si on dépasse la fin du mois, on arrête au dernier jour
            if ($end > $lastDay) {
                $end = $lastDay->copy();
            }
            $weeks[] = [$start->day, $end->day];
            $current = $end->addDay();
        }
        return $weeks;
    }

    public static function calculerCaRealise($userId, $startDate, $endDate)
    {

        $commune = TableauChiffreService::getVentesSum($userId, 'Commune', $startDate, $endDate);
        $normal = TableauChiffreService::getVentesSum($userId, 'Normale', $startDate, $endDate);

        return $commune + $normal;
    }

    public static function getVentesSum($userId, $typeVente, $startDate, $endDate)
    {
        return DB::table('ventes')
            ->where('id_commercial', $userId)
            ->where('type_vente', $typeVente)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('statut', '!=', 3)
            ->sum('ca_ht');
    }

    public static function calculerCaParSemaine($userId, $year, $month)
    {
        $semaines = TableauChiffreService::getSemainesDefinitions($year, $month);

        $caParSemaine = [];

        foreach ($semaines as [$startDay, $endDay]) {
            $startDate = Carbon::createFromDate($year, $month, $startDay)->toDateString();
            $endDate = Carbon::createFromDate($year, $month, $endDay)->toDateString();

            $commune = TableauChiffreService::getVentesSum($userId, 'Commune', $startDate, $endDate);
            $normal = TableauChiffreService::getVentesSum($userId, 'Normale', $startDate, $endDate);

            $caParSemaine[] = $commune + $normal;
        }
        return $caParSemaine;
    }

    public static function calculateGroupeSums(&$groupe)
    {
        $groupe->somme_objectif_ca = $groupe->users->sum('objectif_ca');
        $groupe->somme_objectif_rdv = $groupe->users->sum('objectif_rdv');
        $groupe->somme_ca_realise = $groupe->users->sum('ca_realise');
        $groupe->somme_rdv_total = $groupe->users->sum('rdv_total');
        $groupe->somme_argus_total = $groupe->users->sum('argus_total');
        $groupe->somme_ca_par_semaine = [0, 0, 0, 0, 0, 0];
        $groupe->somme_marge = $groupe->users->sum('marge');
        $groupe->somme_transformation = $groupe->count_rdv > 0
            ? round(($groupe->somme_ventes / $groupe->count_rdv) * 100)
            : null;

        $groupe->somme_deperdition = $groupe->somme_rdv > 0
            ? round((($groupe->somme_cpt) / $groupe->somme_rdv) * 100)
            : 100;

        $groupe->pourcentage_rdv48h = $groupe->somme_rdv_effectues > 0
            ? round(($groupe->somme_rdv48h / $groupe->somme_rdv_effectues) * 100, 2)
            : 0;

        $groupe_denominateur = 0;
        foreach ($groupe->users as $user){
            $groupe_denominateur += $user->denominateurGroupe;
            foreach ($user->ca_par_semaine as $i => $val) {
                $groupe->somme_ca_par_semaine[$i] += $val;
            }
        }
        $groupe->denominateur = $groupe_denominateur;
        $groupe->somme_panier_moyen = $groupe_denominateur > 0 ? $groupe->somme_ca_realise / $groupe_denominateur : 0;
    }

    public static function initializeGlobalTotals()
    {
        return (object)[
            'somme_objectif_ca' => 0,
            'somme_objectif_rdv' => 0,
            'somme_ca_realise' => 0,
            'somme_rdv_total' => 0,
            'somme_argus_total' => 0,
            'somme_ca_par_semaine' => [0, 0, 0, 0, 0, 0],
            'somme_panier_moyen' => 0,
            'somme_ventes' => 0,
            'count_rdv' => 0,
            'somme_cpt' => 0,
            'somme_rdv' => 0,
            'somme_rdv_effectues' => 0,
            'somme_transformation' => null,
            'somme_deperdition' => null,
            'somme_rdv48h' => 0,
            'somme_marge' => 0,
            'denominateur' => 0
        ];
    }

    public static function processGroupes($groupes, $year, $month, $user, $global, $globalSecteur, $isAdmin, $isDirecteur, $isTelepro)
    {
        $groupesAvecUsers = collect();
        [$startDate, $endDate] = TableauChiffreService::getDateRange($year, $month);

        foreach ($groupes as $groupe) {

            TableauChiffreService::loadGroupeUsers($groupe, $startDate, $endDate, $user, $isAdmin, $isDirecteur, $isTelepro);
            TableauChiffreService::calculateGroupeData($groupe, $year, $month);
            TableauChiffreService::updateGlobalTotals($global, $groupe, $globalSecteur);
            $groupesAvecUsers->push($groupe);
        }
        

        return $groupesAvecUsers;
    }

    public static function loadGroupeUsers($groupe, $startDate, $endDate, $user, $isAdmin, $isDirecteur, $isTelepro)
    {
        $usersQuery = TableauChiffreService::buildUsersQuery($groupe, $startDate, $endDate, $user, $isAdmin, $isDirecteur, $isTelepro);
        $groupe->users = $usersQuery->get();
        TableauChiffreService::initializeGroupeSums($groupe);
    }

    public static function buildUsersQuery($groupe, $startDate, $endDate, $user, $isAdmin, $isDirecteur, $isTelepro)
    {
        $usersQuery = User::select('users.id', 'users.name', 'users.nom', 'users.prenom', 'users.objectif_ca',
            'obj.ca as obj_objectif_ca', 'users.objectif_rdv', 'type', 'directeur', 'groupe', 'telepro', 'prospecteur', 
            'junior', 'role', 'contrat', 'users.active', 'nouveau_vendeur', 'date_entree')
            ->leftJoin('objectifs as obj', function($join) use($startDate){
                $join->on('obj.id_commercial', '=', 'users.id')
                ->where('obj.date', '=', $startDate);
            })
            ->where('groupe', $groupe->id)
            /*->whereDate('date_entree', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('delete')->orWhereDate('delete', '>=', $startDate);
            })*/
            ->where(function($query) use ($startDate, $endDate, $groupe) {
                $query->where('users.active', '1')
                ->orWhere(function($q) use ($startDate, $endDate, $groupe) {
                    $q->whereIn('users.id', function($subquery) use ($startDate, $endDate, $groupe) {
                        $subquery->select(DB::raw('DISTINCT id_commercial'))
                            ->from('ventes')
                            ->whereBetween('date', [
                                $startDate,
                                $endDate
                            ])
                            ->where('secteur', $groupe->secteur);
                    })
                    ->where('users.active', '0');
                });
            })
            ->join('secteur_commercials as secteur', function($join) use($groupe){
                $join->on('secteur.id_commercial', '=', 'users.id')
                ->where('secteur.id_secteur', '=', $groupe->secteur);
            })
            ->whereNotIn('role',['admin','administratif'])
            ->orderByDesc('active')
            ->orderByRaw('ISNULL(date_entree) ASC')
            ->orderBy('date_entree')
            ->orderBy('nom');

        if (!$isAdmin && !$isDirecteur) {
            if ($groupe->id !== null) {
                $usersQuery->where('groupe', $groupe->id);
            } else {
                $usersQuery->where('users.id', $user->id);
            }
        }

        if ($isTelepro) {
            $usersQuery->where('users.id', $user->id);
        }

        return $usersQuery;
    }

    public static function initializeGroupeSums($groupe)
    {
        $groupe->somme_rdv_effectues = 0;
        $groupe->somme_ventes = 0;
        $groupe->count_rdv = 0;
        $groupe->somme_cpt = 0;
        $groupe->somme_rdv = 0;
        $groupe->somme_rdv48h = 0;
    }

    public static function calculateGroupeData($groupe, $year, $month)
    {
        foreach ($groupe->users as $index => $user) {
            
            TableauChiffreService::calculateUserData($user, $groupe, $year, $month);
            /*if($user->nb_ventes == 0)
                unset($groupe->users[$index]);*/
        }

        TableauChiffreService::calculateGroupeSums($groupe);
    }

    public static function calculateUserData(&$user, $groupe, $year, $month)
    {
        // Initialisation des objectifs
        $user->objectif_ca = $user->obj_objectif_ca == 0 ? $user->objectif_ca : $user->obj_objectif_ca;
        $user->objectif_rdv = $user->objectif_rdv ?? 0;

        [$startDate, $endDate] = TableauChiffreService::getDateRange($year, $month);

        // Calculs principaux
        $user->ca_realise = TableauChiffreService::calculerCaRealise($user->id, $startDate, $endDate);
        $user->rdv_total = TableauChiffreService::calculerNombreRdv($user->id, $startDate, $endDate);
        $user->argus_total = TableauChiffreService::calculerArgusTotal($user->id, $startDate, $endDate);
        $user->ca_par_semaine = TableauChiffreService::calculerCaParSemaine($user->id, $year, $month);
        $transfoData = TableauChiffreService::transformationRDV($user->id, $startDate, $endDate);
        $user->transformation = $transfoData['taux'];
        $user->nb_ventes = TableauChiffreService::calculerNombreVentes($user->id, $startDate, $endDate);
        $user->deperditionData = TableauChiffreService::calculerDeperdition($user->id, $startDate, $endDate);
        $user->marge = TableauChiffreService::calcMarge($user->id, $startDate, $endDate);
        $user->deperdition = TableauChiffreService::deperditionRDV($user->id, $startDate, $endDate);

        // Calculs dérivés
        $count_ventes = TableauChiffreService::calculerPaniermoyen($user->id, $startDate, $endDate);
        $user->denominateur = 0;
        $user->denominateurGroupe = 0;
        foreach($count_ventes as $vente){
            if($vente->type_vente == "Commune"){
                $user->denominateur += $vente->total;
                $user->denominateurGroupe += $vente->total / 2;
            }else{
                $user->denominateur += $vente->total;
                $user->denominateurGroupe += $vente->total;
            }
        }
        
        $user->panier_moyen = $user->denominateur > 0 ? $user->ca_realise / $user->denominateur : 0;

        $rdv48h = TableauChiffreService::rdv48hDernieres($user->id, $startDate, $endDate);
        $user->rdv48h = $user->rdv_total > 0 ? round(($rdv48h / $user->rdv_total) * 100) : 0;

        /*$user->deperdition = ($user->rdv_total && $user->nb_ventes !== null)
            ? round((($user->rdv_total - $user->nb_ventes) / $user->rdv_total) * 100)
            : null;*/

        // Période d'essai
        $user->is_fin_periode_essai = TableauChiffreService::isFinPeriodeEssai($user->date_entree);

        // Mise à jour des sommes du groupe
        $groupe->somme_rdv48h += $rdv48h;
        $groupe->somme_cpt += $user->deperditionData->cpt ?? 0;
        $groupe->somme_rdv += $user->deperditionData->rdv ?? 0;
        $groupe->somme_ventes += $transfoData['vente'] ?? 0;
        $groupe->count_rdv += $transfoData['cpt'] ?? 0;
        $groupe->somme_rdv_effectues += $user->rdv_total ?? 0;

    }

    public static function calculerNombreRdv($userId, $startDate, $endDate)
    {
        $rdvs = DB::table('clients as c')
            ->join('projets as p', 'p.id_client', '=', 'c.id')
            ->where(function ($query) use ($userId) {
                $query->where('c.rentre_par', $userId)
                 ->orWhere('c.partage_avec', $userId);
            })
            ->where('p.rdv_annule', 0)
            ->whereBetween('p.date_confirmation', [$startDate, $endDate])
            ->selectRaw('count(p.id) as total')
            ->first();

        return $rdvs?->total ?? 0;
    }

    public static function rdv48hDernieres($userId, $startDate, $endDate)
    {
        $results = DB::table('clients as c')
        ->leftJoin('projets as p', 'c.id', '=', 'p.id_client')
        ->select(
            DB::raw("DATE_FORMAT(p.date_confirmation, '%Y-%u') as annee_semaine"),
            DB::raw('COUNT(p.id) as cpt'),
            DB::raw("
                SUM(
                    CASE 
                        WHEN p.date IS NOT NULL 
                            AND p.date_ajout IS NOT NULL
                            AND DATEDIFF(p.date, p.date_ajout) <=
                                CASE 
                                    WHEN DAYOFWEEK(p.date_confirmation) BETWEEN 2 AND 4 THEN 2
                                    WHEN DAYOFWEEK(p.date_confirmation) BETWEEN 5 AND 7 THEN 4
                                    ELSE 2
                                END + 1
                        THEN 1 ELSE 0 
                    END
                ) as rdv48h
            ")
        )
        ->where('c.rentre_par', $userId)
        ->where('p.rdv_annule', 0)
        ->whereBetween('p.date_confirmation', [$startDate, $endDate])
        ->groupBy('annee_semaine')
        ->orderBy('annee_semaine')
        ->get();

        $last48h = 0;

        foreach($results as $data){
            $last48h += $data->rdv48h;
        }
        return $last48h;

    }

    public static function isFinPeriodeEssai($dateEntree)
    {
        $dateEntree = Carbon::parse($dateEntree)->startOfDay();
        $debut = now()->startOfDay()->subMonths(3);
        $fin = now()->startOfDay()->subMonths(3)->addDays(21);
        return $dateEntree->between($debut, $fin);
    }

    public static function calculerArgusTotal($userId, $startDate, $endDate)
    {
        return DB::table('rendez_vous')
            ->where(function ($query) use ($userId) {
                $query->where('id_commercial', $userId)
                    ->orWhere('partage_avec', $userId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->count();
    }

    public static function transformationRDV($userId, $startDate, $endDate)
    {
        $result = DB::table('rendez_vous')
            ->selectRaw("COUNT(id) as cpt, SUM(CASE WHEN resultat = 'Vendu' THEN 1 ELSE 0 END) as vente")
            ->where(function ($query) use ($userId) {
                $query->where('id_commercial', $userId)
                    ->orWhere('partage_avec', $userId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

        $cpt = $result->cpt ?? 0;
        $vente = $result->vente ?? 0;

        $taux =  $cpt > 0 ? round(($vente / $cpt) * 100) : null;

        return [
            'cpt' => $cpt,
            'vente' => $vente,
            'taux' => $taux,
        ];
    }

    public static function deperditionRDV($userId, $startDate, $endDate)
    {
        $result = DB::table('clients as c')
        ->leftJoin('projets as p', 'p.id_client', '=', 'c.id')
        ->select(
            DB::raw('COALESCE(SUM(p.rdv_annule), 0) as cpt'),
            DB::raw('COALESCE(SUM(p.rdv_confirme), 0) as rdv')
        )
        ->where('c.rentre_par', $userId)
        ->where(function($query) use ($startDate, $endDate) {
            $query
                ->where(function($q) use ($startDate, $endDate) {
                    $q->where('p.rdv_confirme', 0)
                    ->whereBetween('p.date_ajout', [$startDate, $endDate]);
                })
                ->orWhere(function($q) use ($startDate, $endDate) {
                    $q->where('p.rdv_confirme', 1)
                    ->where(function($q2) use($startDate, $endDate) {
                        $q2
                            ->whereRaw('DATEDIFF(p.date, p.date_ajout) <= 2')
                            ->whereBetween('p.date_ajout', [$startDate, $endDate])
                        ->orWhere(function($q3) use($startDate, $endDate) {
                            $q3
                                ->whereRaw('DATEDIFF(p.date, p.date_ajout) > 2')
                                ->whereBetween('p.date_confirmation', [$startDate, $endDate]);
                        });
                    });
                });
        })
        ->first();


        $cpt = $result->cpt ?? 0;
        $vente = $result->rdv ?? 0;

        return $cpt > 0 && $vente > 0 ? round(($cpt / $vente) * 100) : null;
    }

    public static function calculerNombreVentes($userId, $startDate, $endDate)
    {
        return DB::table('ventes')
            ->where('id_commercial', $userId)
            ->whereIn('type_vente', ['Commune', 'Normale'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('statut', '!=', 3)
            ->count();
    }

     public static function calculerDeperdition($userId, $startDate, $endDate)
    {
        $result = DB::table('clients as c')
            ->leftJoin('projets as p', 'p.id_client', '=', 'c.id')
            ->select([
                DB::raw('COALESCE(SUM(p.rdv_annule), 0) as cpt'),
                DB::raw('COALESCE(SUM(p.rdv_confirme), 0) as rdv')
            ])
            ->where('c.rentre_par', $userId)
            ->where(function($query) use($startDate, $endDate){
                $query->where(function($subQuery) use($startDate, $endDate){
                    // Cas 1: rdv_confirme = 0 AND date_ajout between dates
                    $subQuery->where('p.rdv_confirme', 0)
                        ->whereBetween('p.date_ajout', [$startDate, $endDate]);
                })
                ->orWhere(function($subQuery) use($startDate, $endDate){
                    // Cas 2: rdv_confirme = 1 avec deux sous-conditions
                    $subQuery->where('p.rdv_confirme', 1)
                        ->where(function($innerQuery) use($startDate, $endDate){
                            $innerQuery->where(function($condition1) use($startDate, $endDate){
                                // Condition 1: datediff <= 2 AND date_ajout between dates
                                $condition1->whereRaw('DATEDIFF(p.date, p.date_ajout) <= 2')
                                    ->whereBetween('p.date_ajout', [$startDate, $endDate]);
                            })
                            ->orWhere(function($condition2) use($startDate, $endDate){
                                // Condition 2: datediff > 2 AND date_confirmation between dates
                                $condition2->whereRaw('DATEDIFF(p.date, p.date_ajout) > 2')
                                    ->whereBetween('p.date_confirmation', [$startDate, $endDate]);
                            });
                        });
                });
            })
            ->first();
        return $result;
    }

    public static function calcMarge($userId, $startDate, $endDate) {
        $result = DB::table('ventes')
            ->where('id_commercial', $userId)
            ->whereIn('type_vente', ['Commune', 'Normale'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('statut', '!=', 3)
            ->sum('montant');

        return $result;
    }

    public static function calculerPaniermoyen($userId, $startDate, $endDate)
    {

        return DB::table('ventes')
            ->select('type_vente', DB::raw('count(*) as total'))
            ->where('id_commercial', $userId)
            ->whereIn('type_vente', ['Commune', 'Normale'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('statut', '!=', 3)
            ->groupBy('type_vente')
            ->get();
    }

    public static function updateGlobalTotals($global, $groupe, $globalSecteur)
    {
        $global->somme_objectif_ca += $groupe->somme_objectif_ca;
        $global->somme_objectif_rdv += $groupe->somme_objectif_rdv;
        $global->somme_ca_realise += $groupe->somme_ca_realise;
        $global->somme_rdv_total += $groupe->somme_rdv_total;
        $global->somme_argus_total += $groupe->somme_argus_total;
        $global->somme_ventes += $groupe->somme_ventes;
        $global->count_rdv += $groupe->count_rdv;
        $global->somme_cpt += $groupe->somme_cpt;
        $global->somme_rdv += $groupe->somme_rdv;
        $global->somme_rdv_effectues += $groupe->somme_rdv_effectues;
        $global->somme_rdv48h += $groupe->somme_rdv48h;
        $global->somme_marge += $groupe->somme_marge;
        $global->denominateur += $groupe->denominateur;
        $global->somme_panier_moyen = ($global->denominateur > 0) ? $global->somme_ca_realise / $global->denominateur : 0;

        foreach($groupe->somme_ca_par_semaine as $i => $val){
            $global->somme_ca_par_semaine[$i] += $val;
        }

        
        $globalSecteur[$groupe->id_secteur]->somme_objectif_ca += $groupe->somme_objectif_ca;
        $globalSecteur[$groupe->id_secteur]->somme_objectif_rdv += $groupe->somme_objectif_rdv;
        $globalSecteur[$groupe->id_secteur]->somme_ca_realise += $groupe->somme_ca_realise;
        $globalSecteur[$groupe->id_secteur]->somme_rdv_total += $groupe->somme_rdv_total;
        $globalSecteur[$groupe->id_secteur]->somme_argus_total += $groupe->somme_argus_total;
        $globalSecteur[$groupe->id_secteur]->somme_ventes += $groupe->somme_ventes;
        $globalSecteur[$groupe->id_secteur]->count_rdv += $groupe->count_rdv;
        $globalSecteur[$groupe->id_secteur]->somme_cpt += $groupe->somme_cpt;
        $globalSecteur[$groupe->id_secteur]->somme_rdv += $groupe->somme_rdv;
        $globalSecteur[$groupe->id_secteur]->somme_rdv_effectues += $groupe->somme_rdv_effectues;
        $globalSecteur[$groupe->id_secteur]->somme_rdv48h += $groupe->somme_rdv48h;
        $globalSecteur[$groupe->id_secteur]->somme_marge += $groupe->somme_marge;
        $globalSecteur[$groupe->id_secteur]->denominateur += $groupe->denominateur;
        $globalSecteur[$groupe->id_secteur]->somme_panier_moyen = ($globalSecteur[$groupe->id_secteur]->denominateur > 0 ) ? $globalSecteur[$groupe->id_secteur]->somme_ca_realise / $globalSecteur[$groupe->id_secteur]->denominateur : 0;
        foreach($groupe->somme_ca_par_semaine as $i => $val){
            $globalSecteur[$groupe->id_secteur]->somme_ca_par_semaine[$i] += $val;
        }
        
    }

    public static function finalizeGlobalTotals($global, $globalSecteur)
    {
        $global->somme_transformation = $global->count_rdv > 0
            ? round(($global->somme_ventes / $global->count_rdv) * 100)
            : null;

        $global->somme_deperdition = $global->somme_rdv > 0
            ? round((($global->somme_cpt) / $global->somme_rdv) * 100)
            : 100;

        $global->pourcentage_rdv48h = $global->somme_rdv_effectues > 0
            ? round(($global->somme_rdv48h / $global->somme_rdv_effectues) * 100, 2)
            : 0;

        foreach($globalSecteur as $secteur){
            $secteur->somme_transformation = $secteur->count_rdv > 0
                ? round(($secteur->somme_ventes / $secteur->count_rdv) * 100)
                : null;

            $secteur->somme_deperdition = $secteur->somme_rdv > 0
                ? round((($secteur->somme_cpt) / $secteur->somme_rdv) * 100)
                : 100;

            $secteur->pourcentage_rdv48h = $secteur->somme_rdv_effectues > 0
                ? round(($secteur->somme_rdv48h / $secteur->somme_rdv_effectues) * 100, 2)
                : 0;
        }
    }

}
