<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Create work schedules for the first user (ID: 1)
        WorkSchedule::create([
            'user_id' => 1,
            'date' => now(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        WorkSchedule::create([
            'user_id' => 1,
            'date' => now()->addDay(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        // Create work schedules for the second user (ID: 2)
        WorkSchedule::create([
            'user_id' => 2,
            'date' => now(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        WorkSchedule::create([
            'user_id' => 2,
            'date' => now()->addDay(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        // Create work schedules for the third user (ID: 3)
        WorkSchedule::create([
            'user_id' => 3,
            'date' => now(),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        WorkSchedule::create([
            'user_id' => 3,
            'date' => now()->addDay(),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);
    }
} 