<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Sorter;
use App\Models\Appointment;
use App\Models\SortingSession;
use App\Models\SortingLog;
use App\Models\Notification;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────────────
        User::create([
            'full_name'     => 'Admin User',
            'email'         => 'admin@tomatosort.com',
            'password_hash' => Hash::make('password'),
            'role'          => 'admin',
        ]);

        // ── Sorters ──────────────────────────────────────────────────────────
        $sorterData = [
            ['full_name' => 'Juan dela Cruz', 'email' => 'juan@sorter.com',  'location' => 'Batangas City'],
            ['full_name' => 'Maria Santos',   'email' => 'maria@sorter.com', 'location' => 'Lipa City'],
            ['full_name' => 'Carlo Reyes',    'email' => 'carlo@sorter.com', 'location' => 'Tanauan City'],
        ];

        $sorters = [];
        foreach ($sorterData as $s) {
            $user = User::create([
                'full_name'     => $s['full_name'],
                'email'         => $s['email'],
                'password_hash' => Hash::make('password'),
                'role'          => 'sorter',
            ]);
            $sorters[] = Sorter::create([
                'user_id'        => $user->id,
                'location'       => $s['location'],
                'contact_number' => '09' . rand(100000000, 999999999),
                'is_available'   => true,
            ]);
        }

        // ── Farmers ──────────────────────────────────────────────────────────
        $farmerData = [
            ['full_name' => 'Pedro Reyes',    'email' => 'pedro@farm.com',  'farm_name' => 'Reyes Farm',     'address' => 'Sto. Tomas, Batangas'],
            ['full_name' => 'Ana Villanueva', 'email' => 'ana@farm.com',    'farm_name' => 'Villa Tomato',   'address' => 'Malvar, Batangas'],
            ['full_name' => 'Ramon Cruz',     'email' => 'ramon@farm.com',  'farm_name' => 'Cruz Harvest',   'address' => 'Padre Garcia, Batangas'],
            ['full_name' => 'Liza Mendoza',   'email' => 'liza@farm.com',   'farm_name' => 'Mendoza Fields', 'address' => 'Rosario, Batangas'],
        ];

        $farmers = [];
        foreach ($farmerData as $f) {
            $user = User::create([
                'full_name'     => $f['full_name'],
                'email'         => $f['email'],
                'password_hash' => Hash::make('password'),
                'role'          => 'farmer',
            ]);
            $farmers[] = Farmer::create([
                'user_id'        => $user->id,
                'farm_name'      => $f['farm_name'],
                'contact_number' => '09' . rand(100000000, 999999999),
                'address'        => $f['address'],
            ]);
        }

        // ── Date blocks with multiple appointments per day ────────────────────
        // Format: [days_from_today, appointments_per_day, status_distribution]
        // status_distribution: [completed, confirmed, pending] counts
        $dayBlocks = [
            // Past dates — all completed with sorting sessions
            [-30, ['completed' => 4, 'confirmed' => 0, 'pending' => 0]],
            [-28, ['completed' => 3, 'confirmed' => 0, 'pending' => 0]],
            [-25, ['completed' => 5, 'confirmed' => 0, 'pending' => 0]],
            [-22, ['completed' => 2, 'confirmed' => 0, 'pending' => 0]],
            [-20, ['completed' => 4, 'confirmed' => 0, 'pending' => 0]],
            [-18, ['completed' => 3, 'confirmed' => 0, 'pending' => 0]],
            [-15, ['completed' => 5, 'confirmed' => 0, 'pending' => 0]],
            [-13, ['completed' => 3, 'confirmed' => 0, 'pending' => 0]],
            [-10, ['completed' => 4, 'confirmed' => 0, 'pending' => 0]],
            [-8,  ['completed' => 2, 'confirmed' => 0, 'pending' => 0]],
            [-6,  ['completed' => 5, 'confirmed' => 0, 'pending' => 0]],
            [-4,  ['completed' => 3, 'confirmed' => 0, 'pending' => 0]],
            [-3,  ['completed' => 4, 'confirmed' => 0, 'pending' => 0]],
            [-2,  ['completed' => 3, 'confirmed' => 0, 'pending' => 0]],
            [-1,  ['completed' => 2, 'confirmed' => 0, 'pending' => 0]],

            // Today & upcoming — mix of confirmed and pending
            [0,   ['completed' => 0, 'confirmed' => 3, 'pending' => 2]],
            [1,   ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [2,   ['completed' => 0, 'confirmed' => 1, 'pending' => 4]],
            [3,   ['completed' => 0, 'confirmed' => 3, 'pending' => 2]],
            [5,   ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [7,   ['completed' => 0, 'confirmed' => 4, 'pending' => 1]],
            [9,   ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [10,  ['completed' => 0, 'confirmed' => 1, 'pending' => 4]],
            [12,  ['completed' => 0, 'confirmed' => 3, 'pending' => 2]],
            [14,  ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [16,  ['completed' => 0, 'confirmed' => 1, 'pending' => 4]],
            [18,  ['completed' => 0, 'confirmed' => 3, 'pending' => 2]],
            [20,  ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [22,  ['completed' => 0, 'confirmed' => 1, 'pending' => 4]],
            [25,  ['completed' => 0, 'confirmed' => 3, 'pending' => 2]],
            [28,  ['completed' => 0, 'confirmed' => 2, 'pending' => 3]],
            [30,  ['completed' => 0, 'confirmed' => 1, 'pending' => 4]],
        ];

        $times = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00'];
        $totalAppointments = 0;

        foreach ($dayBlocks as [$daysOffset, $distribution]) {
            $date = Carbon::today()->addDays($daysOffset)->toDateString();
            $timeIndex = 0;

            foreach ($distribution as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $farmerIdx = ($totalAppointments) % count($farmers);
                    $sorterIdx = ($totalAppointments) % count($sorters);
                    $time      = $times[$timeIndex % count($times)];
                    $timeIndex++;

                    $appointment = Appointment::create([
                        'farmer_id'      => $farmers[$farmerIdx]->id,
                        'sorter_id'      => $sorters[$sorterIdx]->id,
                        'scheduled_date' => $date,
                        'scheduled_time' => $time,
                        'status'         => $status,
                        'notes'          => $status === 'pending' ? 'Please prepare bins.' : null,
                    ]);

                    Notification::create([
                        'user_id'        => $sorters[$sorterIdx]->user_id,
                        'appointment_id' => $appointment->id,
                        'message'        => "New appointment booked for {$date} at {$time}.",
                        'is_read'        => $daysOffset < 0,
                    ]);

                    // Create sorting session for completed appointments
                    if ($status === 'completed') {
                        $ripe   = rand(100, 400);
                        $unripe = rand(30,  150);
                        $rotten = rand(10,  80);
                        $started = Carbon::parse($date . ' ' . $time);
                        $ended   = $started->copy()->addHours(rand(2, 5));

                        $session = SortingSession::create([
                            'appointment_id'  => $appointment->id,
                            'started_at'      => $started,
                            'ended_at'        => $ended,
                            'ripe_count'      => $ripe,
                            'unripe_count'    => $unripe,
                            'rotten_count'    => $rotten,
                            'raspberry_pi_id' => 'RPI-00' . ($sorterIdx + 1),
                            'session_status'  => 'completed',
                        ]);

                        // Sample individual logs
                        $classifications = array_merge(
                            array_fill(0, min($ripe, 10),   'ripe'),
                            array_fill(0, min($unripe, 5),  'unripe'),
                            array_fill(0, min($rotten, 3),  'rotten'),
                        );
                        shuffle($classifications);

                        foreach ($classifications as $j => $classification) {
                            SortingLog::create([
                                'session_id'            => $session->id,
                                'logged_at'             => $started->copy()->addMinutes($j * 3),
                                'tomato_classification' => $classification,
                                'image_path'            => "/captures/{$session->id}/tomato_{$j}.jpg",
                                'ai_confidence'         => round(rand(85, 99) / 100, 2),
                            ]);
                        }
                    }

                    $totalAppointments++;
                }
            }
        }

        $this->command->info('✅ Seeded successfully!');
        $this->command->info("📅 Total appointments created: {$totalAppointments}");
        $this->command->info('👤 Admin:   admin@tomatosort.com / password');
        $this->command->info('🌾 Farmers: pedro@farm.com, ana@farm.com, ramon@farm.com, liza@farm.com / password');
        $this->command->info('⚙️  Sorters: juan@sorter.com, maria@sorter.com, carlo@sorter.com / password');
    }
}