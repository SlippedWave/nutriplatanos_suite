<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Guest routes (only accessible when not authenticated)
Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')->name('login');
});

// Protected routes (only accessible when authenticated)
Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::redirect('configuracion', 'configuracion/perfil')->name('settings');
    Volt::route('configuracion/perfil', 'settings.profile')->name('settings.profile');
    Volt::route('configuracion/clave', 'settings.password')->name('settings.password');

    Volt::route('rutas', 'routes.index')->name('routes.index');
    Volt::route('rutas/historial', 'routes.history')->name('routes.history');
    Volt::route('rutas/detalles/{route}', 'routes.show')->name('routes.show');

    Route::middleware(['password.confirm'])->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Volt::route('configuracion/usuarios', 'settings.users')->name('settings.users');
        });

        Route::middleware(['role:admin,coordinator'])->group(function () {
            Volt::route('clientes', 'customers.index')->name('customers.index');
            Volt::route('clientes/detalles/{customer}', 'customers.show')->name('customers.show');
        });
    });
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

require __DIR__ . '/auth.php';
