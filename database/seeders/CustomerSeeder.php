<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name' => 'Supermercado La Comer',
            'address' => 'Av. Insurgentes Sur 1234, Ciudad de México',
            'phone' => '555-1234',
            'email' => 'contacto@lacomer.com.mx',
            'rfc' => 'LACO123456ABC',
        ]);

        Customer::create([
            'name' => 'Tienda Soriana',
            'address' => 'Blvd. Manuel Ávila Camacho 456, Naucalpan',
            'phone' => '555-2345',
            'email' => 'contacto@soriana.com.mx',
            'rfc' => 'SORI123456ABC',
        ]);

        Customer::create([
            'name' => 'Mercado Central',
            'address' => 'Calle Central 789, Ciudad de México',
            'phone' => '555-3456',
            'email' => 'ventas@mercadocentral.com.mx',
            'rfc' => 'MERC123456ABC',
        ]);
    }
} 