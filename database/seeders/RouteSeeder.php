<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        Route::create([
            'name' => 'Ruta Norte',
            'description' => 'Ruta que cubre las zonas norte de la Ciudad de México',
            'start_location' => 'Bodega Central',
            'end_location' => 'Centro de Distribución Norte',
            'estimated_time' => '2:30:00',
            'distance' => 45.5,
        ]);

        Route::create([
            'name' => 'Ruta Sur',
            'description' => 'Ruta que cubre las zonas sur de la Ciudad de México',
            'start_location' => 'Bodega Sur',
            'end_location' => 'Centro de Distribución Sur',
            'estimated_time' => '3:00:00',
            'distance' => 52.3,
        ]);

        Route::create([
            'name' => 'Ruta Oriente',
            'description' => 'Ruta que cubre las zonas oriente de la Ciudad de México',
            'start_location' => 'Bodega Principal',
            'end_location' => 'Centro de Distribución Oriente',
            'estimated_time' => '2:15:00',
            'distance' => 38.7,
        ]);
    }
} 