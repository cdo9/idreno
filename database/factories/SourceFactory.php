<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->word(),
            'groupe' => 'actifs',
        ];
    }

    public function passive(): static
    {
        return $this->state(fn (array $attributes) => ['groupe' => 'passif']);
    }

    public function telepro(): static
    {
        return $this->state(fn (array $attributes) => ['groupe' => 'télépro']);
    }
}
