<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => null,
            'latitude' => fake()->latitude(-6.5, -6.1),
            'longitude' => fake()->longitude(106.6, 107.0),
            'speed' => fake()->randomFloat(2, 0, 100),
            'recorded_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }
}
