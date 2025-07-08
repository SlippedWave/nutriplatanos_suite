<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        Product::create([
            'name' => 'Plátano Orgánico',
            'description' => 'Plátanos orgánicos frescos y de alta calidad.',
            'price' => 1.50,
        ]);
        Product::create([
            'name' => 'Plátano Convencional',
            'description' => 'Plátanos convencionales, perfectos para el día a día.',
            'price' => 1.00,
        ]);
    }
}
