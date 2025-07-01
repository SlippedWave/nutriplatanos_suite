<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@nutriplatanos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
            'phone' => '5512345678',
            'curp' => 'ADMS123456HDFABC01',
            'rfc' => 'ADMS123456ABC',
            'address' => 'Av. Reforma 123, Ciudad de México',
            'emergency_contact' => 'María González',
            'emergency_contact_phone' => '5587654321',
            'emergency_contact_relationship' => 'Esposa',
        ]);

        // Create some regular users
        User::create([
            'name' => 'Juan Pérez Martínez',
            'email' => 'juan@nutriplatanos.com',
            'password' => Hash::make('password'),
            'role' => 'coordinator',
            'active' => true,
            'phone' => '5523456789',
            'curp' => 'PEMJ123456HDFABC02',
            'rfc' => 'PEMJ123456ABC',
            'address' => 'Calle Juárez 456, Ciudad de México',
            'emergency_contact' => 'Ana Pérez',
            'emergency_contact_phone' => '5598765432',
            'emergency_contact_relationship' => 'Esposa',
        ]);

        User::create([
            'name' => 'María Rodríguez Sánchez',
            'email' => 'maria@nutriplatanos.com',
            'password' => Hash::make('password'),
            'role' => 'carrier',
            'active' => true,
            'phone' => '5534567890',
            'curp' => 'ROSM123456HDFABC03',
            'rfc' => 'ROSM123456ABC',
            'address' => 'Av. Insurgentes 789, Ciudad de México',
            'emergency_contact' => 'Carlos Rodríguez',
            'emergency_contact_phone' => '5587654321',
            'emergency_contact_relationship' => 'Esposo',
        ]);
    }
}
