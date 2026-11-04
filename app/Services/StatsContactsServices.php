<?php

namespace App\Services;

class StatsContactsServices
{
    public function __construct() {}

    public static function ClientTypes(): array
    {
        return [
            6 => 'Refus',
            7 => 'Absent',
            8 => 'Relance',
            0 => 'Précontact',
            1 => 'Prospect',
            2 => 'Client',
            //        4 => null,
            5 => 'En pose',
            3 => 'Annulé',
        ];
    }
}
