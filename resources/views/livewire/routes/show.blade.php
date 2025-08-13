<?php

use Livewire\Volt\Component;
use App\Models\Route;
use App\Services\RouteService;

new class extends Component {
    public $selectedRoute;
    public $showCloseRouteModal = false;
    public $showEditRouteModal = false;

    public $title = '';

    protected RouteService $routeService;

    public function boot(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function mount()
    {
        // Fetch the route ID from the request and load the route
        // Assuming the route ID is passed as a route parameter named 'route'
        $routeId = request()->route('route');
        $this->selectedRoute = Route::findOrFail($routeId);
    }

    public function openEditRouteModal()
    {
        $this->title = $this->selectedRoute->title; // Pre-fill the title with the current route title
        $this->showEditRouteModal = true;
    }

    public function openCloseRouteModal()
    {
        $this->showCloseRouteModal = true;
    }

    public function closeModals()
    {
        $this->showCloseRouteModal = false;
        $this->showEditRouteModal = false;
    }

    public function updateRoute()
    {
        if (!$this->selectedRoute) {
            session()->flash('error', 'No se ha seleccionado ninguna ruta.');
            return;
        }

        $result = $this->routeService->editRoute($this->selectedRoute, ['title' => $this->title]);

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function closeRoute()
    {
        $routeService = app(RouteService::class);
        $result = $routeService->closeRoute($this->selectedRoute);
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showCloseRouteModal = false;
            return redirect()->route('routes.index');
        } else {
            session()->flash('error', $result['message']);
        }
    }
};
?>
<section class="w-full">
    <x-layouts.routes.layout :heading="$selectedRoute->title ?? 'Detalles de la Ruta'" :subheading="'Información detallada de la ruta creada el ' . $selectedRoute->created_at->format('d/m/Y')">
        @if (session()->has('message'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex justify-between items-center">
                <div>{{ session('message') }}</div>
                <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif
    
        @if (session()->has('error'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-4 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg flex justify-between items-center">
                <div>{{ session('error') }}</div>
                <button type="button" @click="show = false" class="text-danger-500 hover:text-danger-700">
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Detalles de la Ruta</h2>
            @if($selectedRoute->isActive())
            <div>
                <flux:button.group>
                    <flux:button variant="primary" 
                        class="bg-secondary-400! hover:bg-secondary-300!"
                        icon="pencil"
                        wire:click="openEditRouteModal">
                        Editar ruta
                    </flux:button>
                    <flux:button 
                        variant="primary"
                        icon="folder"
                        class="hover:bg-primary-200!" 
                        wire:click="openCloseRouteModal">
                        Cerrar ruta
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
            @livewire('expenses.tables.expenses-table', ['route_id' => $selectedRoute->id])
        </div>

    @include('components.routes.close-route-modal', ['selectedRoute' => $selectedRoute])
    @include('components.routes.edit-route-modal')

    </x-layouts.routes.layout>
</section>