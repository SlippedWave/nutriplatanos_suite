<?php

use Livewire\Volt\Component;
use App\Models\Route;
use App\Services\RouteService;
use Livewire\Attributes\On;

new class extends Component {
    public $selectedRoute;

    protected RouteService $routeService;

    protected $listeners = [
        'routes-info-updated' => '$refresh',
    ];

    public function boot(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function mount()
    {
        $this->selectedRoute = Route::findOrFail(request()->route('route'));
        if (session()->has('banner')) {
            $this->dispatch('show-message-banner', [
                'text' => session('banner.text'),
                'type' => session('banner.type'),
                'duration' => session('banner.duration', 5000),
                'bannerId' => session('banner.bannerId', 'default'),
            ]);
        }
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

    <flux:separator class="my-6" />

        {{-- Box summary + movements history --}}
        <div class="mt-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Movimientos de Cajas</h3>
            </div>

            @php
                $boxSummary = $selectedRoute->getBoxSummary();
            @endphp

            {{-- Summary card --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-blue-500 font-medium uppercase tracking-wide">Tomadas de cámaras</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $boxSummary['taken_from_cameras'] }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-green-500 font-medium uppercase tracking-wide">Entregadas a clientes</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $boxSummary['delivered_to_customers'] }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-yellow-600 font-medium uppercase tracking-wide">Recibidas de clientes</p>
                    <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $boxSummary['returned_by_customers'] }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-purple-500 font-medium uppercase tracking-wide">Devueltas a cámaras</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $boxSummary['returned_to_cameras'] }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-orange-500 font-medium uppercase tracking-wide">Enviadas a rutas</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $boxSummary['sent_to_routes'] }}</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-teal-500 font-medium uppercase tracking-wide">Recibidas de rutas</p>
                    <p class="text-2xl font-bold text-teal-700 mt-1">{{ $boxSummary['received_from_routes'] }}</p>
                </div>
                {{-- Boxes with product: what the carrier still has available to deliver. --}}
                <div class="bg-gray-800 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-300 font-medium uppercase tracking-wide">Con producto en camión</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $boxSummary['net_full_on_truck'] }}</p>
                </div>
                {{-- Empties hold no product but remain the carrier's responsibility. --}}
                <div class="bg-gray-100 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Vacías en camión</p>
                    <p class="text-2xl font-bold text-gray-700 mt-1">{{ $boxSummary['net_empty_on_truck'] }}</p>
                </div>
            </div>

            {{-- Movements history --}}
            @php
                $movements = $selectedRoute->boxMovementsForDisplay();
            @endphp

            @if($movements->isNotEmpty())
                <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cámara</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruta contraparte</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cajas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contenido</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($movements as $movement)
                                    @php
                                        $superseded = $movement->trashed();
                                        $typeColors = [
                                            'warehouse_to_route'  => 'blue',
                                            'route_to_warehouse'  => 'purple',
                                            'route_to_route'      => 'yellow',
                                            'truck_inventory'     => 'gray',
                                        ];
                                        $color = $typeColors[$movement->movement_type] ?? 'gray';
                                        $typeLabel = \App\Models\BoxMovement::MOVEMENT_TYPES[$movement->movement_type] ?? $movement->movement_type;
                                        $contentLabel = \App\Models\BoxMovement::BOX_CONTENT_STATUSES[$movement->box_content_status] ?? $movement->box_content_status;
                                    @endphp
                                    <tr class="{{ $superseded ? 'opacity-40' : 'hover:bg-gray-50' }}">
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $movement->camera?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($movement->movement_type === 'route_to_route')
                                                @php
                                                    $counterpart = $movement->counterpartRouteFor($selectedRoute->id);
                                                    $direction   = $movement->transferDirectionFor($selectedRoute->id);
                                                    $dirLabel     = \App\Models\BoxMovement::TRANSFER_DIRECTIONS[$direction] ?? '';
                                                    $counterpartId = (int) $movement->route_id === (int) $selectedRoute->id
                                                        ? $movement->related_route_id
                                                        : $movement->route_id;
                                                @endphp
                                                <span class="text-gray-500">{{ $dirLabel }}</span>
                                                {{ $counterpart->title ?? ('Ruta #' . $counterpartId) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-center font-semibold">
                                            {{ $movement->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $contentLabel }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $movement->moved_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($superseded)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    Invalidado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    Confirmado
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-400">No hay movimientos de cajas registrados para esta ruta.</p>
            @endif
        </div>

        <livewire:routes.update-route-modal />
        <livewire:routes.close-route-modal />
    </x-layouts.routes.layout>
</section>