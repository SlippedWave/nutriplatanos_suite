<div class="space-y-6">
    <!-- Flash Messages -->
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'routes-table')
        @php
            $type = data_get($flash, 'type', 'info');
        @endphp

        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition
            @class([
                'px-4 py-3 rounded-lg flex justify-between items-center',
                'bg-green-50 border border-green-200 text-green-700' => $type === 'success',
                'bg-danger-50 border border-danger-200 text-danger-700' => $type === 'error',
                'bg-yellow-50 border border-yellow-200 text-yellow-700' => $type === 'warning',
                'bg-blue-50 border border-blue-200 text-blue-700' => !in_array($type, ['success','error','warning']),
            ])
        >
            <div>{{ data_get($flash, 'text') }}</div>
            <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <!-- Header with Search and Filters -->
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar rutas..."
                    type="search"
                />
            </div>
            
            <div class="flex-1 gap-4">
                <flux:button 
                    variant="primary" 
                    wire:click="toggleIncludeDeleted"
                    class="{{ $includeDeleted ? 'bg-gray-100! text-gray-900!' : 'bg-background! text-gray-500! hover:bg-gray-50!' }}"
                >
                    {{ $includeDeleted ? __('Ocultar eliminadas') : __('Incluir eliminadas') }}
                </flux:button>

                <flux:select wire:model.live="perPage" class="w-20">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </flux:select>

                <livewire:routes.create-route-modal />
            </div>
        </div>

        <!-- Filters Row -->
        <div class="flex gap-4">
            <div class="flex-1">
                <flux:select wire:model.live="statusFilter" placeholder="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="active">Activas</option>
                    <option value="closed">Cerradas</option>
                </flux:select>
            </div>
            
            @if($carriers->isNotEmpty())
                <div class="flex-1">
                    <flux:select wire:model.live="carrierFilter" placeholder="Filtrar por transportista">
                        <option value="">Todos los transportistas</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center">
                            <button wire:click="sortBy('title')" class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Título
                                @if($sortField === 'title')
                                    <flux:icon.chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="size-3" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Estado
                                @if($sortField === 'status')
                                    <flux:icon.chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="size-3" />
                                @endif
                            </button>
                        </th>
                        @if(in_array(auth()->user()->role, ['admin', 'coordinator']))
                        <th class="px-6 py-3 text-center">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Transportista</span>
                        </th>
                        @endif
                        <th class="px-6 py-3 text-center">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Fecha de Inicio
                                @if($sortField === 'created_at')
                                    <flux:icon.chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="size-3" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</span>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($routes as $route)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $route->title ?: 'Ruta del ' . $route->created_at->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'closed' => 'gray',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Activa',
                                        'closed' => 'Cerrada',
                                        'cancelled' => 'Cancelada'
                                    ];
                                    $color = $statusColors[$route->status] ?? 'gray';
                                    $label = $statusLabels[$route->status] ?? $route->status;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ $label }}
                                </span>
                            </td>
                            @if(in_array(auth()->user()->role, ['admin', 'coordinator']))
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $route->carrier->name ?? 'Sin asignar' }}
                                </div>
                            </td>
                            @endif
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $route->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $route->sales()->count() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center gap-2 justify-center">
                                    <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            wire:click="openViewModal({{ $route->id }})"
                                        />
                                        
                                        @if($route->status === 'active')
                                            <flux:button 
                                                variant="ghost" 
                                                size="sm" 
                                                icon="pencil"
                                                wire:click="openEditModal({{ $route->id }})"
                                            />
                                            <flux:button 
                                                variant="ghost" 
                                                size="sm" 
                                                icon="stop"
                                                class="text-warning-600 hover:text-warning-700 hover:bg-warning-50"
                                                wire:click="openCloseModal({{ $route->id }})"
                                            />
                                        @endif
                                        
                                        @if ($route->trashed())
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-danger-100 text-danger-800">
                                            Eliminada
                                        </span>
                                        @else
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash"
                                            class="text-danger-600 hover:text-danger-700 hover:bg-danger-50"
                                            wire:click="openDeleteModal({{ $route->id }})"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array(auth()->user()->role, ['admin', 'coordinator']) ? 6 : 5 }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <flux:icon.map class="size-12 text-gray-300 mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay rutas</h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        @if($search)
                                            No se encontraron rutas que coincidan con "{{ $search }}"
                                        @else
                                            Comienza creando tu primera ruta.
                                        @endif
                                    </p>
                                    @if(!$search)
                                        <flux:button variant="primary" wire:click="$dispatch('open-create-route-modal')">
                                            <flux:icon.plus class="size-4" />
                                            Nueva Ruta
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($routes->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $routes->links() }}
            </div>
        @endif
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between text-sm text-gray-500">
        <div>
            Mostrando {{ $routes->firstItem() ?? 0 }} - {{ $routes->lastItem() ?? 0 }} 
            de {{ $routes->total() }} rutas
        </div>
        
        @if($search)
            <button wire:click="$set('search', '')" class="text-primary-600 hover:text-primary-700">
                Limpiar búsqueda
            </button>
        @endif
    </div>

    <!-- Modals -->
    @include('components.routes.edit-route-modal', ['selectedRoute' => $selectedRoute])
{{--     @include('components.routes.create-route-modal', ['showCreateModal' => $showCreateModal])
 --}}    @include('components.routes.view-route-modal', ['selectedRoute' => $selectedRoute])
    @include('components.routes.delete-route-modal', ['selectedRoute' => $selectedRoute])
    @include('components.routes.close-route-modal', ['selectedRoute' => $selectedRoute])
</div>