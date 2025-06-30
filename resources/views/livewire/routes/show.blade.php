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
    <x-welcome-section welcome-message="Mostrando información de la ruta." />
    <div class="mt-2 w-full max-w-full overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Nombre:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->title ?? 'Ruta creada en ' . $route->created_at->format('d/m/Y') }}</p>
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
            
            
            @if($route->date)
            <div>
                <p class="text-sm font-medium text-gray-500">Fecha de trabajo:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->date }}</p>
            </div>
            @endif
            
            <div>
                <p class="text-sm font-medium text-gray-500">Fecha de Creación:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $route->created_at->format('d/m/Y') }}</p>
            </div>



        </div>
    </div>

    <!-- Sales History Section -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ventas registradas en la ruta:</h3>
        @livewire('sells.tables.sells-table', ['route_id' => $route->id])
    </div>