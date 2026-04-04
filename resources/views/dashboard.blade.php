<?php

use App\Models\Customer;
use App\Models\Camera;
use App\Models\Sale;
use App\Models\Route;

$activeCustomers = Customer::where('active', true)->count();
$monthlyGrossRevenue = Sale::whereMonth('created_at', now()->month)->sum('paid_amount');
$activeRoutes = Route::where('status', 'active')->count();
$completedRoutes = Route::where('status', 'completed')->where('updated_at', '>=', now()->startOfWeek())->count();

?>

<x-layouts.app :title="__('Panel de Control - Nutriplatanos')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <x-welcome-section welcome-message="Gestiona los recursos del negocio desde una sola plataforma. Aquí podrás ver un resumen de tu negocio, administrar productos, clientes y ventas." />

        <!-- Quick Stats -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 md:mb-6">
            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'coordinator')
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6 cursor-pointer" href="{{ route('customers.index') }}" wire:navigate>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Clientes Activos</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{$activeCustomers}}</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <flux:icon.user-group />
                    </div>
                </div>
            </div>
            @endif
            
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Cámaras disponibles</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{ Camera::count() }}</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <flux:icon.home-modern />
                    </div>
                </div>
            </div>
        
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'coordinator')
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Ganancias brutas del mes</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{ $monthlyGrossRevenue }}</p>
                        @else
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Ganancias brutas de rutas</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{ auth()->user()->sales()->whereMonth('created_at', now()->month)->sum('paid_amount') }}</p>
                        @endif
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <flux:icon.currency-dollar />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2 md:mb-6">
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6 cursor-pointer" href="{{ route('routes.index') }}" wire:navigate>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Rutas activas</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{$activeRoutes}}</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <flux:icon.truck />
                    </div>
                </div>
            </div>

            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6 cursor-pointer" href="{{ route('routes.history') }}" wire:navigate>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Rutas completadas esta semana</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{ $completedRoutes }}</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <flux:icon.globe-americas />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
