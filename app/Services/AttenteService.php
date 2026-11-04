<?php

namespace App\Services;

class AttenteService
{
    /**
     * Get a list of waiting statuses
     */
    public static function getList(): array
    {
        return [
            'Devis',
            'Financement',
            'Acompte',
            'Commande',
            'Pose',
            'Autre',
        ];
    }
}
