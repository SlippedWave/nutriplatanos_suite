<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {};
?>

<section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-welcome-section welcome-message="Gestiona los recursos del negocio desde aquí." />

    <div class="bg-white border border-gray-300 rounded-md p-4 shadow-sm">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <flux:icon.home-modern class="w-6 h-6 text-gray-500" />
                <div>
                    <p class="text-sm font-semibold text-gray-800">Cámaras</p>
                    <p class="text-xs text-gray-500">Cámaras disponibles</p>
                </div>
            </div>
        </div>

        <div class="border-t pt-3">
            <div class="overflow-x-auto -mx-4 px-4">
                <livewire:resources.cameras.tables.cameras-table />
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-300 rounded-md p-4 shadow-sm mt-4">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <flux:icon.home-modern class="w-6 h-6 text-gray-500" />
                <div>
                    <p class="text-sm font-semibold text-gray-800">Productos</p>
                    <p class="text-xs text-gray-500">Productos disponibles para la venta</p>
                </div>
            </div>
        </div>

        <div class="border-t pt-3">
            <div class="overflow-x-auto -mx-4 px-4">
                <livewire:resources.products.tables.products-table />
            </div>
        </div>
    </div>



</section>
