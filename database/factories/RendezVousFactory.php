<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\RendezVous;
use App\Models\Secteur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RendezVous>
 */
class RendezVousFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_client' => Client::factory(),
            'id_commercial' => User::factory(),
            'resultat' => 'Vendu',
            'date' => today(),
            'relance_confirme' => 0,
            'source' => '1',
            'tarif_total_ht' => 20000,
            'devis_total_ht' => 20000,
            'secteur' => Secteur::factory(),
        ];
    }
}
