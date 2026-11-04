<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SourceService
{
    private static array $sourceNames = [];

    private static array $sourceList = [];

    public function __construct() {}

    /**
     * Legacy sources mapping.
     */
    public static function createSourceLst(): array
    {
        if (! empty(static::$sourceList)) {
            return static::$sourceList;
        }
        $sources = DB::table('sources')
            ->orderBy('groupe')  // Groupé d'abord
            ->orderBy('id')
            ->get()
            ->groupBy('groupe')
            ->mapWithKeys(function ($group, $groupe) {
                return [$groupe => $group->pluck('nom', 'id')->toArray()];
            })
            ->toArray();

        // Fusionne avec vos clés par défaut si groupes manquants
        static::$sourceList = array_merge([
            'actifs' => [],
            'passif' => [],
            'télépro' => [],
        ], $sources);

        return static::$sourceList;
    }

    public static function getSourceName($id): string
    {
        $id = (int) $id;  // Sécurité

        // 1. Cache local instantané
        if (isset(static::$sourceNames[$id])) {
            return static::$sourceNames[$id];
        }

        static::$sourceNames = DB::table('sources')->pluck('nom', 'id')->toArray();

        return static::$sourceNames[$id] ?? '';
    }
}
