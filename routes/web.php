<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Redirect root to login for guests, dashboard for authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/design-system', function () {
    return view('components.design-system-examples');
})->name('design-system');

// Guest routes (only accessible when not authenticated)
Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')->name('login');
});

// Protected routes (only accessible when authenticated)
Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::redirect('configuracion', 'configuracion/perfil')->name('settings');
    Volt::route('configuracion/perfil', 'settings.profile')->name('settings.profile');
    Volt::route('configuracion/contraseña', 'settings.password')->name('settings.password');

    Route::middleware(['role:admin', 'password.confirm'])->group(function () {
        Volt::route('configuracion/usuarios', 'settings.users')->name('settings.users');
    });
});

require __DIR__ . '/auth.php';
