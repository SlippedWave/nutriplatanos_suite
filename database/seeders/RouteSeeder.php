<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        // Get carrier users (assuming they have role 'carrier' or similar)
        $carriers = User::where('role', 'carrier')->get();

        if ($carriers->isEmpty()) {
            // If no carriers exist, get any users and treat them as carriers
            $carriers = User::take(3)->get();
        }

        if ($carriers->isNotEmpty()) {
            Route::create([
                'date' => now()->format('Y-m-d'),
                'carrier_id' => $carriers->first()->id,
            ]);

            Route::create([
                'date' => now()->addDay()->format('Y-m-d'),
                'carrier_id' => $carriers->count() > 1 ? $carriers->get(1)->id : $carriers->first()->id,
            ]);

            Route::create([
                'date' => now()->addDays(2)->format('Y-m-d'),
                'carrier_id' => $carriers->count() > 2 ? $carriers->get(2)->id : $carriers->first()->id,
            ]);
        }
    }
}
