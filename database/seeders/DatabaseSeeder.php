<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            WorkScheduleSeeder::class,
            CustomerSeeder::class,
            RouteSeeder::class,
        ]);
    }
} 