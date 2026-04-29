<?php

use Livewire\Volt\Component;
use App\Models\Route;
use App\Services\RouteService;
use Livewire\Attributes\On;

new class extends Component {
    public $selectedRoute;

    protected RouteService $routeService;

    public $listeners = [
        'routes-info-updated' => '$refresh',
    ];

    public function boot(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function mount()
    {
        $this->selectedRoute = Route::findOrFail(request()->route('route'));
    }
};
?>
<section class="w-full">
    <livewire:alerts.message-banner banner-id="routes" />
    <x-layouts.routes.layout :heading="$selectedRoute->title ?? 'Detalles de la Ruta'" :subheading="'Información detallada de la ruta creada el ' . $selectedRoute->created_at->format('d/m/Y')">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Detalles de la Ruta</h2>
            @if($selectedRoute->isActive())
            <div>
                <flux:button.group>
                    <flux:button variant="primary" 
                        class="bg-secondary-400! hover:bg-secondary-300!"
                        icon="pencil"
                        wire:click="$dispatch('open-update-route-modal', { id: {{ $selectedRoute->id }} })">
                        <span class="hidden sm:inline">Editar ruta</span>
                        <span class="sm:hidden">Editar</span>
                    </flux:button>
                    <flux:button 
                        variant="primary"
                        icon="folder"
                        class="hover:bg-primary-200!" 
                        wire:click="$dispatch('open-close-route-modal', { id: {{ $selectedRoute->id }} })">
                        <span class="hidden sm:inline">Cerrar ruta</span>
                        <span class="sm:hidden">Cerrar</span>
                    </flux:button>
                </flux:button.group>
            </div>
            @endif
        </div>
        
        <div class="mt-2 w-full max-w-full overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nombre:</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $selectedRoute->title ?? 'Ruta creada en ' . $selectedRoute->created_at->format('d/m/Y') }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500">Inició:</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $selectedRoute->created_at->format('d/m/Y H:i') }}</p>
                </div>

                @if($selectedRoute->status)
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado:</p>
                    <p class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2 bg-{{ $selectedRoute->getStatusColorAttribute() }}-100 text-{{ $selectedRoute->getStatusColorAttribute() }}-800">
                            {{ $selectedRoute->getStatusLabelAttribute() }}
                        </span>
                    </p>
                </div>
                @endif

                @if($selectedRoute->carrier)
                <div>
                    <p class="text-sm font-medium text-gray-500">Transportista:</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $selectedRoute->carrier->name }}</p>
                </div>
                @endif
            </div>
        </div>

        @livewire('notes.notes-displayer', ['notable_type' => Route::class, 'notable_id' => $selectedRoute->id])

        <!-- Sales History Section -->
        <div class="mt-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Historial de Ventas en Ruta
                </h3>
            </div>
            @livewire('sales.tables.sales-table', ['route_id' => $selectedRoute->id])
        </div>
    <flux:separator class="my-6" />

        <div class="mt-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Historial de Gastos en Ruta</h3>
            </div>
            <div class="py-4">
                <livewire:alerts.message-banner banner-id="expenses" />
            </div>
            @livewire('accounting.expenses.tables.expenses-table', ['route_id' => $selectedRoute->id])
        </div>

        <livewire:routes.update-route-modal />
        <livewire:routes.close-route-modal />
    </x-layouts.routes.layout>
</section>