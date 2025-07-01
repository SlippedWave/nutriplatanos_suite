<?php

use Livewire\Volt\Component;
use App\Models\Route;

new class extends Component {
    public $route;

    public function mount()
    {
        $routeId = request()->route('route');
        $this->route = Route::findOrFail($routeId);
    }
};
?>

<section class="w-full">
    <x-layouts.routes.layout :heading="$route->title ?? 'Detalles de la Ruta'" :subheading="'Información detallada de la ruta creada el ' . $route->created_at->format('d/m/Y')">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Detalles de la Ruta</h2>
            <div>
                <flux:button.group>
                    <flux:button variant="primary" 
                        class="bg-secondary-400! hover:bg-secondary-300!"
                        wire:click="$emit('openModal', 'routes.edit-route-modal', ['route' => $route])">
                        Editar ruta
                    </flux:button>
                    <flux:button 
                        variant="primary"
                        class="hover:bg-primary-200!" 
                        wire:click="$emit('openModal', 'routes.add-sell-modal', ['route' => $route])">
                        Cerrar ruta
                    </flux:button>
                </flux:button.group>
            </div>
        </div>
    <div class="mt-2 w-full max-w-full overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Nombre:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->title ?? 'Ruta creada en ' . $route->created_at->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-500">Inició:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->created_at->format('d/m/Y H:i') }}</p>
            </div>

            @if($route->status)
            <div>
                <p class="text-sm font-medium text-gray-500">Estado:</p>
                <p class="mt-1 text-sm text-gray-900">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2 bg-{{ $route->getStatusColorAttribute() }}-100 text-{{ $route->getStatusColorAttribute() }}-800">
                        {{ $route->status_label }}
                    </span>
                </p>
            </div>
            @endif

            @if($route->carrier_name)
            <div>
                <p class="text-sm font-medium text-gray-500">Transportista:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->carrier_name }}</p>
            </div>
            @endif

        </div>
    </div>

    <div class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notas:</h3>
            <flux:button 
                variant="primary" 
                class="bg-secondary-400! hover:bg-secondary-300!"
                icon="plus"
                wire:click="$emit('openModal', 'routes.add-note-modal', ['route' => $route])">
                Añadir nota
            </flux:button>
        </div>
        <ul class="list-disc pl-5 space-y-2">
            @forelse ($route->notes() as $note)
                <li class="text-sm text-gray-700">
                    <span class="font-semibold">{{ $note->created_at->format('d/m/Y H:i') }}:</span> {{ $note->content }}
                </li>
            @empty
                <li class="text-sm text-gray-500">No hay notas registradas para esta ruta.</li>
            @endforelse
        </ul>
        
    </div>

    <!-- Sales History Section -->
    <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ventas registradas en la ruta:</h3>
        @livewire('sells.tables.sells-table', ['route_id' => $route->id])
    </div>
    <!-- End Sales History Section -->


    </x-layouts.routes.layout>
</section>