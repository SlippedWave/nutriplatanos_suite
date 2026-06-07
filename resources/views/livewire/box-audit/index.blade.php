<?php

use Livewire\Volt\Component;
use App\Models\Camera;
use App\Models\BoxMovement;
use App\Models\CameraStockAdjustment;
use App\Models\Customer;
use Livewire\Attributes\On;

new class extends Component {
    #[On('box-audit-updated')]
    #[On('box-balance-adjustment-saved')]
    public function refresh(): void {}
};
?>

<section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-welcome-section welcome-message="Auditoría de movimientos de cajas." />

    <livewire:alerts.message-banner banner-id="box-audit" />

    @php
        $cameras = Camera::orderBy('name')->get();
        $totalBoxesInCameras = $cameras->sum(fn($c) => max(0, $c->getCurrentStock()));
        $totalMovements = BoxMovement::count();
        $totalAdjustments = CameraStockAdjustment::count();
        $todayOut = BoxMovement::where('movement_type', 'warehouse_to_route')
            ->whereDate('moved_at', today())->sum('quantity');
        $todayIn = BoxMovement::where('movement_type', 'route_to_warehouse')
            ->whereDate('moved_at', today())->sum('quantity');

        $customersWithBalance = Customer::where('active', true)
            ->get()
            ->map(fn($c) => ['customer' => $c, 'balance' => $c->getBoxBalance()])
            ->filter(fn($row) => $row['balance'] !== 0)
            ->sortByDesc('balance')
            ->values();
        $totalBoxesWithCustomers = $customersWithBalance->sum('balance');
    @endphp

    {{-- Dashboard cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Cajas en cámaras</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalBoxesInCameras }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs text-orange-400 font-medium uppercase tracking-wide">Cajas con clientes</p>
            <p class="text-3xl font-bold text-orange-700 mt-1">{{ max(0, $totalBoxesWithCustomers) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs text-blue-400 font-medium uppercase tracking-wide">Salidas hoy</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ $todayOut }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs text-purple-400 font-medium uppercase tracking-wide">Entradas hoy</p>
            <p class="text-3xl font-bold text-purple-700 mt-1">{{ $todayIn }}</p>
        </div>
    </div>

    {{-- Camera stock breakdown --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <flux:icon.home-modern class="w-5 h-5 text-gray-400" />
                <p class="text-sm font-semibold text-gray-700">Stock por cámara</p>
            </div>
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="$dispatch('open-stock-adjustment-modal')"
                size="sm"
            >
                Ajuste manual
            </flux:button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($cameras as $camera)
                @php $stock = $camera->getCurrentStock(); @endphp
                <div class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $camera->name }}</p>
                        <p class="text-xs text-gray-400">{{ $camera->location }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold {{ $stock <= 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $stock }}</p>
                        <p class="text-xs text-gray-400">cajas</p>
                    </div>
                    <flux:button
                        variant="ghost"
                        size="xs"
                        icon="plus"
                        wire:click="$dispatch('open-stock-adjustment-modal', { cameraId: {{ $camera->id }} })"
                        class="ml-2"
                    />
                </div>
            @endforeach
        </div>
    </div>

    {{-- Customer box balances --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <flux:icon.users class="w-5 h-5 text-gray-400" />
                <p class="text-sm font-semibold text-gray-700">Saldos de cajas por cliente</p>
            </div>
        </div>
        @if($customersWithBalance->isEmpty())
            <p class="text-sm text-gray-400">Todos los clientes tienen saldo en cero.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($customersWithBalance as $row)
                    <div class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $row['customer']->name }}</p>
                        </div>
                        <div class="flex items-center gap-3 ml-3 shrink-0">
                            <div class="text-right">
                                <p class="text-xl font-bold {{ $row['balance'] < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $row['balance'] }}
                                </p>
                                <p class="text-xs text-gray-400">cajas</p>
                            </div>
                            <flux:button
                                variant="ghost"
                                size="xs"
                                icon="pencil-square"
                                wire:click="$dispatch('open-box-balance-adjustment-modal', { customerId: {{ $row['customer']->id }} })"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- All movements table --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.arrows-right-left class="w-5 h-5 text-gray-400" />
            <p class="text-sm font-semibold text-gray-700">Historial de movimientos</p>
        </div>
        <livewire:box-audit.box-movements-table />
    </div>

    <livewire:box-audit.create-stock-adjustment-modal />
    <livewire:customers.create-box-balance-adjustment-modal />
</section>
