<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por transportista o título..."
                type="search"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>
        
        <div class="flex gap-2">
            <!-- Status Filter -->
            <flux:select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <flux:select.option value="all">Todos los estados</flux:select.option>
                <flux:select.option value="Pendiente">Pendiente</flux:select.option>
                <flux:select.option value="En Progreso">En Progreso</flux:select.option>
                <flux:select.option value="Archivada">Archivada</flux:select.option>
                <flux:select.option value="Cancelada">Cancelada</flux:select.option>
                <flux:select.option value="Eliminada">Eliminada</flux:select.option>
            </flux:select>
            
            <!-- Date Filter -->
            <flux:select wire:model.live="dateFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <flux:select.option value="all">Todas las fechas</flux:select.option>
                <flux:select.option value="today">Hoy</flux:select.option>
                <flux:select.option value="week">Esta semana</flux:select.option>
                <flux:select.option value="month">Este mes</flux:select.option>
            </flux:select>
            
            <!-- Per Page -->
            <flux:select wire:model.live="perPage" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
        </div>
    </div>

    @if($dateFilter !== 'all' && $startDate && $endDate)
    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <span class="font-medium">Filtro activo:</span>
                    @if($dateFilter === 'today')
                        Rutas de hoy ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'week')
                        Rutas de esta semana ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'month')
                        Rutas de este mes ({{ \Carbon\Carbon::parse($startDate)->format('M Y') }})
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

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-center">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('carrier_name')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Transportista
                            <span class="ml-2">
                                @if($sortField === 'carrier_name')
                                    @if($sortDirection === 'asc')
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('title')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Título de Ruta
                            <span class="ml-2">
                                @if($sortField === 'title')
                                    @if($sortDirection === 'asc')
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('date')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Fecha
                            <span class="ml-2">
                                @if($sortField === 'date')
                                    @if($sortDirection === 'asc')
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('route_status')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Estado de la Ruta
                            <span class="ml-2">
                                @if($sortField === 'route_status')
                                    @if($sortDirection === 'asc')
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @endif
                            </span>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($routes as $route)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <div class="flex flex-items-center gap-3 justify-center">
                                    <div>
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-sm">
                                            {{ $route->carrier?->initials() ?? 'NA' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        {{ $route->carrier_name ?? 'Sin asignar' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $route->title ?? 'Sin título' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $route->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{$route->getStatusColorAttribute()}}-100 
                                    text-{{$route->getStatusColorAttribute()}}-800">
                                    {{ $route->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center gap-2 justify-center">
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm" 
                                        icon="eye"
                                        wire:click="openViewModal({{ $route->id }})"
                                        aria-label="{{ __('Ver ruta') }}"
                                    />
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm" 
                                        icon="trash"
                                        class="text-danger-600 hover:text-danger-700 hover:bg-danger-50"
                                        wire:click="openDeleteModal({{ $route->id }})"
                                        aria-label="{{ __('Eliminar usuario') }}"
                                    />
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron rutas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @include('components.routes.view-route-modal')
    @include('components.routes.delete-route-modal')
</div>