<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Password confirmation route (only for authenticated users)
Route::middleware('auth')->group(function () {
    Volt::route('confirm-password', 'auth.confirm-password')    
        ->name('password.confirm');
});

// Logout route
Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
