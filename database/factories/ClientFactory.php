<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Secteur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'adresse' => fake()->streetAddress(),
            'cp' => fake()->numerify('#####'),
            'ville' => fake()->city(),
            'telephone1' => fake()->numerify('06########'),
            'mail' => fake()->safeEmail(),
            'rentre_par' => User::factory(),
            'debrief' => '',
            'type' => 1,
            'mode_conso' => '',
            'proprietaire' => true,
            'relance' => false,
            'bloque' => 0,
            'secteur' => Secteur::factory(),
        ];
    }

    public function sharedWith(User $user): static
    {
        return $this->state(fn (array $attributes) => ['partage_avec' => $user->id]);
    }
}
