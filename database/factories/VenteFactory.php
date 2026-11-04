<?php

namespace Database\Factories;

use App\Models\RendezVous;
use App\Models\Secteur;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vente>
 */
class VenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rdv' => RendezVous::factory(),
            'id_client' => fn (array $attributes) => RendezVous::find($attributes['id_rdv'])->id_client,
            'id_commercial' => User::factory(),
            'tarif_total_ht' => 20000,
            'ca_total_ht' => 20000,
            'repartition' => 100,
            'tarif_ht' => 20000,
            'ca_ht' => 20000,
            'remise' => 0,
            'montant' => 0,
            'plus_value' => 0,
            'prime_cee' => 0,
            'date' => today(),
            'statut' => 1,
            'secteur' => Secteur::factory(),
        ];
    }

    public function forRendezVous(RendezVous $rendezVous): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rdv' => $rendezVous->id,
            'id_client' => $rendezVous->id_client,
        ]);
    }

    public function withCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'commission' => 1,
            'date_commission' => today()->subMonth(),
        ]);
    }
}
