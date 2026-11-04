<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'commercial',
            'contrat' => 'commercial-v1',
            'objectif_ca' => 0,
            'objectif_rdv' => 0,
            'type' => 0,
            'groupe' => 0,
            'telepro' => 0,
            'directeur' => 0,
            'active' => 1,
            'ordre_affichage' => 0,
            'failed_logins' => false,
            'registration_ip' => '127.0.0.1',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function directeur(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'directeur', 'contrat' => 'directeur-v1']);
    }

    public function telepro(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'telepro', 'contrat' => 'telepro-v1']);
    }

    public function releasedToday(): static
    {
        return $this->state(fn (array $attributes) => ['released' => today()]);
    }
}
