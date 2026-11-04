<?php

namespace App\Utils;

class DataNormalizer
{
    /**
     * Convertit les valeurs vides en null dans un tableau de données
     *
     * @param  array  $data  Le tableau de données à normaliser
     * @return array Le tableau avec les valeurs vides converties en null
     */
    public static function normalizeEmptyValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '' || $value === []) {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
