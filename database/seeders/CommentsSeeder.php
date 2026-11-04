<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommentsSeeder extends Seeder
{
    public function run()
    {
        DB::table('comments')->insert([
            [
                'client_id' => 1,
                'user_id' => 1,
                'commentaire' => 'Premier contact téléphonique effectué avec le client. Informations générales validées.',
                'date' => Carbon::now()->subDays(2),
            ],
            [
                'client_id' => 1,
                'user_id' => 2,
                'commentaire' => 'Rendez-vous fixé pour le 20/06/2024 à 14h. Le client souhaite discuter des solutions d\'isolation.',
                'date' => Carbon::now()->subDay(),
            ],
            [
                'client_id' => 2,
                'user_id' => 1,
                'commentaire' => 'Envoi du devis par email. Relance prévue sous 7 jours.',
                'date' => Carbon::now(),
            ],
        ]);
    }
} 