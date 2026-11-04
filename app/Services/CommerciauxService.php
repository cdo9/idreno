<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CommerciauxService
{
    protected static $allCommerciaux = null;
    protected static $allCommerciauxBySector = null;
    protected static $allCommerciauxWithInactive = null;
    protected static $allCommerciauxWithInactiveBySector = null;

    public static function getCommerciauxListBySector($orderBy = null, $orderDirection = null){
        if (static::$allCommerciauxBySector === null) {
            static::$allCommerciauxBySector = Cache::remember('all_commerciaux_indexed_by_groupes_'.session('selectedSecteur').'_'.$orderBy.'_'.$orderDirection, 3600, function() use($orderBy, $orderDirection){
                $users = User::where('active', 1)
                    ->join('secteur_commercials as secteur','secteur.id_commercial', 'users.id')
                    ->whereNotIn('role',["administratif"])
                    ->where('secteur.id_secteur', session('selectedSecteur'));
                if($orderBy)
                    $users->orderby('users.'.$orderBy, $orderDirection);
                else
                    $users->orderBy('users.nom','asc')->orderBy('users.prenom', 'asc');
                return    $users->get();
            });
        }

        return static::$allCommerciauxBySector;
        
    }

    public static function getEntireCommerciauxList($orderBy = null, $orderDirection = null)
    {
        if (static::$allCommerciaux === null) {
            static::$allCommerciaux = Cache::remember('all_commerciaux_indexed_'.$orderBy.'_'.$orderDirection, 3600, function()use($orderBy, $orderDirection){
                $users =  User::where('active', 1)->whereNotIn('role',["administratif"]);

                if($orderBy)
                    $users->orderby('users.'.$orderBy, $orderDirection);
                else
                    $users->orderBy('users.nom','asc')->orderBy('users.prenom', 'asc');
                    
            
                return $users->get()->keyBy('id');
            });
        }

        return static::$allCommerciaux;
    }

    public static function getEntireCommerciauxListWithInactiveBySector()
    {
        if (static::$allCommerciauxWithInactiveBySector === null) {
            static::$allCommerciauxWithInactiveBySector = Cache::remember('all_commerciaux_withInactiveBySector_indexed_'.session('selectedSecteur'), 3600, function () {
                return User::orderBy('nom', 'asc')
                    ->join('secteur_commercials as secteur','secteur.id_commercial', 'users.id')
                    ->where('secteur.id_secteur', session('selectedSecteur'))
                    ->whereNotIn('role',["administratif"])
                    ->orderBy('prenom', 'asc')
                    ->get()
                    ->keyBy('id');  // ✅ Indexé par ID
            });
        }

        return static::$allCommerciauxWithInactiveBySector;
    }

    public static function getEntireCommerciauxListWithInactive()
    {
        if (static::$allCommerciauxWithInactive === null) {
            static::$allCommerciauxWithInactive = Cache::remember('all_commerciaux_withInactive_indexed', 3600, function () {
                return User::whereNotIn('role',["administratif"])->orderBy('nom', 'asc')
                    ->orderBy('prenom', 'asc')
                    ->get()
                    ->keyBy('id');  // ✅ Indexé par ID
            });
        }

        return static::$allCommerciauxWithInactive;
    }

    public static function getCommercialById($id)
    {
        return static::getEntireCommerciauxListWithInactive()->get($id);
    }
}
