<?php

use App\Models\Customer;

$activeCustomers = Customer::where('active', true)->count();

?>

<x-layouts.app :title="__('Panel de Control - Nutriplatanos')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <x-welcome-section welcome-message="Gestiona los recursos del negocio desde una sola plataforma. Aquí podrás ver un resumen de tu negocio, administrar productos, clientes y ventas." />

        <!-- Quick Stats -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-6">
            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'coordinator')
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6 cursor-pointer" href="{{ route('customers.index') }}" wire:navigate>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Clientes Activos</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">{{$activeCustomers}}</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Productos en Stock</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">0</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-[var(--color-background)] rounded-xl border border-[var(--color-gray-200)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-gray-600)]">Ventas del Mes</p>
                        <p class="text-2xl font-bold text-[var(--color-text)]">$0</p>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-[var(--color-primary)] bg-opacity-10 flex items-center justify-center">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v1a3 3 0 006 0v-1c0-1.657-1.343-3-3-3z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20h14a2 2 0 002-2v-7a9 9 0 10-18 0v7a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-[var(--color-gray-200)] bg-[var(--color-background)] p-6">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-[var(--color-text)] mb-2">
                    Próximamente
                </h3>
                <p class="text-[var(--color-gray-600)]">
                    Aquí podrás ver gráficos, reportes y análisis de tu negocio
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
