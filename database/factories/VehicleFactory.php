<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plate_number' => fake()->unique()->bothify('B #### ??'),
            'type' => fake()->randomElement(['truck', 'car', 'motorcycle']),
            'user_uuid' => null,
        ];
    }
}
