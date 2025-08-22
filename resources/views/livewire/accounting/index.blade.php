<?php

use Livewire\Volt\Component;

new class extends Component {
    public $dateFilter = 'all';

    public $totalExpensesAmount = 0;
}; ?>


<section class="w-full">
    <x-welcome-section welcome-message="Gestiona la contabilidad del negocio desde aquí." />
        <flux:select wire:model.live="dateFilter" class="flex-1 xs:min-w-[140px]">
            <option value="all">Todas las fechas</option>
            <option value="today">Hoy</option>
            <option value="week">Esta semana</option>
            <option value="month">Este mes</option>
        </flux:select>

        
        @if($dateFilter !== 'all')
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <flux:icon.calendar class="w-5 h-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">Filtro activo:</span>
                            @if($dateFilter === 'today')
                                Gastos de hoy ({{ \Carbon\Carbon::parse(now()->startOfDay()->toDateString())->format('d/m/Y') }})
                            @elseif($dateFilter === 'week')
                                Gastos de esta semana ({{ \Carbon\Carbon::parse(now()->startOfWeek()->toDateString())->format('d/m') }} - {{ \Carbon\Carbon::parse(now()->endOfWeek()->toDateString())->format('d/m/Y') }})
                            @elseif($dateFilter === 'month')
                                Gastos de este mes ({{ \Carbon\Carbon::parse(now()->startOfMonth()->toDateString())->format('M Y') }})
                            @endif
                        </p>
                    </div>
                    <div class="ml-auto">
                        <button wire:click="$set('dateFilter', 'all')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Limpiar filtro
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 w-full max-w-full overflow-hidden">
        <div class="overflow-x-auto">
                <livewire:sales.tables.sales-table
                    wire:model.live="dateFilter"
                    hideFilters="true"
                />
        </div>
        <div class="mt-6 w-full max-w-full overflow-hidden">
            <div class="overflow-x-auto">
                    <livewire:accounting.tables.expenses-table
                        wire:model.live="dateFilter"
                        hideFilters="true"
                    />
            </div>
        </div>
    </div>
    
</section>

