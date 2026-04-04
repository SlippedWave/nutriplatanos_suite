<?php

namespace Database\Seeders;

use App\Models\Camera;
use Illuminate\Database\Seeder;

class CameraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Camera::create([
            'name' => 'Camera 1',
            'location' => 'Location 1',
            'box_stock' => 442,
        ]);

        Camera::create([
            'name' => 'Camera 2',
            'location' => 'Location 2',
            'box_stock' => 150,
        ]);

        Camera::create([
            'name' => 'Camera 3',
            'location' => 'Location 3',
            'box_stock' => 75,
        ]);

        Camera::create([
            'name' => 'Camera 4',
            'location' => 'Location 4',
            'box_stock' => 200,
        ]);

        Camera::create([
            'name' => 'Camera 5',
            'location' => 'Location 5',
            'box_stock' => 100,
        ]);
    }
}
