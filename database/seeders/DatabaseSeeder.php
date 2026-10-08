<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\TrackingLog;
use App\Models\Event;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin user
        $admin = User::firstOrCreate([
            'username' => 'admin',
        ], [
            'nama' => 'Administrator',
            'password' => Hash::make('password'),
        ]);

        // Generate additional random users
        $users = User::factory()->count(5)->create();
        
        // Include admin in the users pool
        $users->push($admin);

        // For each user, let's create 1 or 2 vehicles
        foreach ($users as $user) {
            $vehicles = Vehicle::factory()->count(rand(1, 2))->create([
                'user_uuid' => $user->uuid,
            ]);

            // For each vehicle, create some tracking logs and events
            foreach ($vehicles as $vehicle) {
                TrackingLog::factory()->count(10)->create([
                    'vehicle_id' => $vehicle->id,
                ]);

                Event::factory()->count(2)->create([
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        }
    }
}
