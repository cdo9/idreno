<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "1" => [
                1 => "tap",
                2 => "gms",
                3 => "ancien client",
                5 => "phone",
                6 => "parrainage",
                7 => "foire",
                9 => "marché",
                19 => "flyers",
                21 => "recommandation",
                23 => "apporteur d'affaire",
                22 => "prospecteur",
                24 => "journée portes ouvertes",
            ],
            "2" => [
                18 => "leads",
                20 => "salarié",
                15 => "facebook",
                13 => "internet",
                14 => "mailing",
                4 => "magasin",
            ],
            "3" => [
                12 => "télépro",
                16 => "ancien client télépro",
                17 => "parrainage télépro",
            ],
        ];

        foreach ($data as $groupe => $sources) {
            foreach ($sources as $id => $nom) {
                DB::table('sources')->updateOrInsert(
                    ['id' => $id],
                    ['nom' => $nom, 'groupe' => $groupe]
                );

                DB::table('clients')
                    ->where(function($query) use ($id) {
                        $query->where('id_source', 0)
                              ->orWhereNull('id_source');
                    })
                    ->where('source', $nom)
                    ->update(['id_source' => $id]);
            }
        }
    }
}
