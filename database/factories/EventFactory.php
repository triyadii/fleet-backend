<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => null,
            'event_type' => fake()->randomElement(['emergency', 'speeding', 'idle', 'maintenance']),
            'description' => fake()->sentence(),
        ];
    }
}
