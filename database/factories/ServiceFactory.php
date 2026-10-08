<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => 'Cuci '.fake()->unique()->word(),
            'unit' => 'kg',
            'price' => fake()->randomElement([6000, 7000, 8000, 10000]),
            'duration_days' => 2,
            'is_active' => true,
        ];
    }
}
