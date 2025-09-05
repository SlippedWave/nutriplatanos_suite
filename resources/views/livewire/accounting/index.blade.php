<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On; 

new class extends Component {
    public $dateFilter = 'all';

    public $totalExpensesAmount = 0;
    public $totalPaymentsAmount = 0;

    public $netProfit = 0;

    #[On('expensesTotalUpdated')]
    public function onExpensesTotalUpdated($value) { $this->totalExpensesAmount = $value; $this->getNetProfit(); }

    #[On('paymentsTotalUpdated')]
    public function onPaymentsTotalUpdated($value) { $this->totalPaymentsAmount = $value; $this->getNetProfit(); }

    public function getNetProfit()
    {
        $this->netProfit = $this->totalPaymentsAmount - $this->totalExpensesAmount;
    }
}; ?>

<section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-welcome-section welcome-message="Gestiona la contabilidad del negocio desde aquí." />

    <div class="mt-4">
        <flux:select wire:model.live="dateFilter" class="w-full sm:w-64">
            <option value="all">Todas las fechas</option>
            <option value="today">Hoy</option>
            <option value="week">Esta semana</option>
            <option value="month">Este mes</option>
        </flux:select>
    </div>

    @if($dateFilter !== 'all')
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-md p-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center text-blue-700">
                    <flux:icon.calendar class="w-5 h-5 text-blue-400 mr-2" />
                    <p class="text-sm">
                        <span class="font-medium">Filtro activo:</span>
                        @if($dateFilter === 'today')
                            Hoy ({{ \Carbon\Carbon::parse(now()->startOfDay()->toDateString())->format('d/m/Y') }})
                        @elseif($dateFilter === 'week')
                            Esta semana ({{ \Carbon\Carbon::parse(now()->startOfWeek()->toDateString())->format('d/m') }} - {{ \Carbon\Carbon::parse(now()->endOfWeek()->toDateString())->format('d/m/Y') }})
                        @elseif($dateFilter === 'month')
                            Este mes ({{ \Carbon\Carbon::parse(now()->startOfMonth()->toDateString())->format('M Y') }})
                        @endif
                    </p>
                </div>
               <div class="sm:ml-auto">
                    <button wire:click="$set('dateFilter', 'all')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Limpiar filtro
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white border rounded-md p-4 flex items-center border-green-700 bg-green-50">
            <div class="flex-shrink-0">
                <flux:icon.currency-dollar class="w-6 h-6 text-green-500" />
            </div>
            <div class="ml-3">
                <p class="text-xs text-gray-500">Total recibido (pagos)</p>
                <p id="payments-total" class="text-xl font-semibold text-green-700">${{ number_format($totalPaymentsAmount, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-md p-4 flex items-center border-red-700 bg-red-50">
            <div class="flex-shrink-0">
                <flux:icon.currency-dollar class="w-6 h-6 text-red-500" />
            </div>
            <div class="ml-3">
                <p class="text-xs text-gray-500">Total gastado (gastos)</p>
                <p id="expenses-total" class="text-xl font-semibold text-red-700">${{ number_format($totalExpensesAmount, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="bg-white border rounded-md p-4 flex border-gray-300 items-center bg-gray-50">
            <div class="flex-shrink-0">
                <flux:icon.currency-dollar class="w-6 h-6 text-gray-500" />
            </div>
            <div class="ml-3">
                <p class="text-xs text-gray-500">Utilidad neta</p>
                <p id="net-profit" class="text-xl font-semibold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format($netProfit, 2) }}
                </p>
            </div>
            <div class="ml-auto">
                @if($netProfit >= 0)
                    <span class="text-sm px-2 py-1 bg-green-100 text-green-800 rounded-full">Positiva</span>
                @else
                    <span class="text-sm px-2 py-1 bg-red-100 text-red-800 rounded-full">Negativa</span>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        <div class="grid grid-cols-1 gap-4">
            <!-- Payments card -->
            <div class="bg-white border border-gray-300 rounded-md p-4 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <flux:icon.currency-dollar class="w-6 h-6 text-green-500" />
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Desglose de pagos</p>
                            <p class="text-xs text-gray-500">Lista de ingresos según el filtro seleccionado</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-green-50 text-green-700 rounded-full">
                            Pagos
                        </span>
                    </div>
                </div>

                <div class="border-t pt-3">
                    <div class="overflow-x-auto -mx-4 px-4">
                        <livewire:accounting.payments.tables.payments-table wire:model.live="dateFilter" />
                    </div>
                </div>
            </div>

            <!-- Expenses card -->
            <div class="bg-white border rounded-md p-4 border-gray-300 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <flux:icon.currency-dollar class="w-6 h-6 text-red-500" />
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Desglose de gastos</p>
                            <p class="text-xs text-gray-500">Registro de egresos según el filtro seleccionado</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded-full">Gastos</span>
                    </div>
                </div>

                <div class="border-t pt-3">
                    <div class="overflow-x-auto -mx-4 px-4">
                        <livewire:accounting.expenses.tables.expenses-table
                            wire:model.live="dateFilter"
                            hideFilters="true"
                        />
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>