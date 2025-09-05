<div class="space-y-6">
    <!-- Flash Messages -->
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'expenses-table')
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

    @if(!$hideFilters)
    <div class="flex flex-col gap-4">
        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <!-- Filters Group -->
            <div class="flex flex-col xs:flex-row gap-2 flex-1">
                <!-- Search Bar (Full Width) -->
                <div class="w-full">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar gastos..."
                        type="search"
                        class="w-full"
                    />
                </div>
                <!-- Date Filter -->
                <flux:select wire:model.live="dateFilter" class="flex-1 xs:min-w-[140px]">
                    <option value="all">Todas las fechas</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </flux:select>

                <!-- Per Page -->
                <flux:select wire:model.live="perPage" class="xs:w-16 flex-none">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </flux:select>
            </div>
            
            <!-- Action Buttons Group -->
            <div class="flex flex-col xs:flex-row gap-2 xs:items-center">
                <flux:button 
                    variant="primary" 
                    wire:click="toggleIncludeDeletedExpenses"
                    class="{{ $includeDeletedExpenses ? 'bg-danger-100! text-danger-900!' : 'bg-background! text-danger-500! hover:bg-danger-50!' }}" 
                    aria-label="{{ $includeDeletedExpenses ? __('Ocultar gastos eliminados') : __('Incluir gastos eliminados') }}"
                    size="sm"
                >
                    <span class="hidden sm:inline">{{ $includeDeletedExpenses ? __('Ocultar eliminadas') : __('Incluir eliminadas') }}</span>
                    <span class="sm:hidden">{{ $includeDeletedExpenses ? __('Ocultar') : __('Eliminadas') }}</span>
                </flux:button>

                @if ($canCreateNewExpense)
                <flux:button variant="primary"
                                icon="plus" 
                                wire:click="$dispatch('open-create-expense-modal')"
                                class="w-full xs:w-auto">
                    <span class="hidden sm:inline">{{ __('Crear nuevo gasto') }}</span>
                    <span class="sm:hidden">{{ __('Crear') }}</span>
                </flux:button>
                @endif
            </div>
        </div>
    </div>


    <!-- Date Range Indicator -->
    @if($dateFilter !== 'all' && $startDate && $endDate)
    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <flux:icon.calendar class="w-5 h-5 text-blue-400" />
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <span class="font-medium">Filtro activo:</span>
                    @if($dateFilter === 'today')
                        Gastos de hoy ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'week')
                        Gastos de esta semana ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'month')
                        Gastos de este mes ({{ \Carbon\Carbon::parse($startDate)->format('M Y') }})
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
    @endif

    <!-- Table -->
    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('created_at')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Fecha</span>
                                @if($sortField === 'created_at')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>

                        @if (!$contextRouteId)
                        <th wire:click="sortBy('route_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Ruta</span>
                                @if($sortField === 'route_id')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        @endif

                        @if (!$contextUserId)
                        <th wire:click="sortBy('user_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Usuario</span>
                                @if($sortField === 'user_id')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        @endif
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Descripción
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cantidad
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50 {{ $expense->trashed() ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $expense->created_at->format('d/m/Y H:i') }}
                                @if($expense->trashed())
                                    <div class="text-xs text-red-500 mt-1">Eliminada</div>
                                @endif
                            </td>

                            @if(!$contextRouteId)        
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $expense->route->title ?? 'Ruta eliminada' }}
                                </div>
                            </td>
                            @endif

                            @if(!$contextUserId)
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $expense->user->name ?? 'Usuario eliminado' }} 
                                </div>
                            </td>
                            @endif

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $expense->description }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                ${{ number_format($expense->amount, 2) }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        wire:click="$dispatch('open-view-expense-modal', { expenseId: {{ $expense->id }} })"
                                        icon="eye"
                                        aria-label="Ver venta"
                                    />
                                    
                                    @if(!$expense->trashed())
                                        
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost" 
                                            wire:click="$dispatch('open-update-expense-modal', { expenseId: {{ $expense->id }} })"
                                            icon="pencil"
                                            aria-label="Editar venta"
                                        />
                                        
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost" 
                                            wire:click="$dispatch('open-delete-expense-modal', { expenseId: {{ $expense->id }} })"
                                            icon="trash"
                                            aria-label="Eliminar venta"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                @if($search)
                                    No se encontraron gastos que coincidan con "{{ $search }}".
                                @else
                                    No hay gastos registrados.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($expenses->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between text-sm text-gray-500">
        <div>
            Mostrando {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} 
            de {{ $expenses->total() }} gastos
        </div>
        
        @if($search)
            <button 
                wire:click="$set('search', '')" 
                class="text-primary-600 hover:text-primary-700"
            >
                Limpiar búsqueda
            </button>
        @endif
    </div>

    <!-- Total Summary -->
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-blue-900">
                Total de gastos:
            </span>
            <span class="text-lg font-bold text-blue-900">
                ${{ number_format($totalAmount, 2) }}
            </span>
        </div>
    </div>

    <!-- Modals -->
    <livewire:accounting.expenses.create-expense-modal
        :context-user-id="$contextUserId"
        :context-route-id="$contextRouteId"
    />
    <livewire:accounting.expenses.update-expense-modal
        :context-user-id="$contextUserId"
        :context-route-id="$contextRouteId"
    />
    <livewire:accounting.expenses.view-expense-modal/>
    <livewire:accounting.expenses.delete-expense-modal/>
</div>
