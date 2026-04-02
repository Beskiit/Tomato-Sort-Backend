<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Sorter;
use App\Models\Appointment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'full_name'     => 'Admin User',
            'email'         => 'admin@tomatosort.com',
            'password_hash' => Hash::make('password'),
            'role'          => 'admin',
        ]);

        // Sorters
        $sorterUsers = [
            ['full_name' => 'Juan dela Cruz', 'email' => 'juan@sorter.com', 'location' => 'Batangas City'],
            ['full_name' => 'Maria Santos',   'email' => 'maria@sorter.com', 'location' => 'Lipa City'],
        ];

        foreach ($sorterUsers as $s) {
            $user = User::create([
                'full_name'     => $s['full_name'],
                'email'         => $s['email'],
                'password_hash' => Hash::make('password'),
                'role'          => 'sorter',
            ]);
            Sorter::create([
                'user_id'        => $user->id,
                'location'       => $s['location'],
                'contact_number' => '09' . rand(100000000, 999999999),
                'is_available'   => true,
            ]);
        }

        // Farmers
        $farmerData = [
            ['full_name' => 'Pedro Reyes',   'email' => 'pedro@farm.com',  'farm_name' => 'Reyes Farm',   'address' => 'Sto. Tomas, Batangas'],
            ['full_name' => 'Ana Villanueva','email' => 'ana@farm.com',    'farm_name' => 'Villa Tomato', 'address' => 'Malvar, Batangas'],
        ];

        foreach ($farmerData as $f) {
            $user = User::create([
                'full_name'     => $f['full_name'],
                'email'         => $f['email'],
                'password_hash' => Hash::make('password'),
                'role'          => 'farmer',
            ]);
            Farmer::create([
                'user_id'        => $user->id,
                'farm_name'      => $f['farm_name'],
                'contact_number' => '09' . rand(100000000, 999999999),
                'address'        => $f['address'],
            ]);
        }

        // Sample appointments
        $farmer = Farmer::first();
        $sorter = Sorter::first();

        Appointment::create([
            'farmer_id'      => $farmer->id,
            'sorter_id'      => $sorter->id,
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '08:00:00',
            'status'         => 'pending',
            'notes'          => 'Please bring extra bins.',
        ]);

        Appointment::create([
            'farmer_id'      => $farmer->id,
            'sorter_id'      => $sorter->id,
            'scheduled_date' => now()->subDays(2)->toDateString(),
            'scheduled_time' => '09:30:00',
            'status'         => 'completed',
        ]);

        $this->command->info('✅ Seeded: 1 admin, 2 sorters, 2 farmers, 2 appointments');
        $this->command->info('📧 All passwords: password');
    }
}
