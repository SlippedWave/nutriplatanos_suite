<div class="space-y-6">
    <!-- Flash Messages -->
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'sales-table')
        @php
            $type = data_get($flash, 'type', 'info');
        @endphp

        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition @class([
            'px-4 py-3 rounded-lg flex justify-between items-center',
            'bg-green-50 border border-green-200 text-green-700' => $type === 'success',
            'bg-danger-50 border border-danger-200 text-danger-700' =>
                $type === 'error',
            'bg-yellow-50 border border-yellow-200 text-yellow-700' =>
                $type === 'warning',
            'bg-blue-50 border border-blue-200 text-blue-700' => !in_array($type, [
                'success',
                'error',
                'warning',
            ]),
        ])>
            <div>{{ data_get($flash, 'text') }}</div>
            <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 sm:p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar ventas..." type="search"
                        class="w-full" />
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <flux:button variant="primary" wire:click="toggleIncludeDeleted"
                        class="{{ $includeDeleted ? 'bg-gray-100! text-gray-900!' : 'bg-background! text-gray-500! hover:bg-gray-50!' }}"
                        aria-label="{{ $includeDeleted ? __('Ocultar ventas eliminadas') : __('Incluir ventas eliminadas') }}"
                        size="sm">
                        <span class="hidden sm:inline">{{ $includeDeleted ? __('Ocultar eliminadas') : __('Incluir eliminadas') }}</span>
                        <span class="sm:hidden">{{ $includeDeleted ? __('Ocultar') : __('Eliminadas') }}</span>
                    </flux:button>

                    <flux:select wire:model.live="dateFilter" class="flex-1 xs:min-w-[140px]">
                        <option value="all">Todas las fechas</option>
                        <option value="today">Hoy</option>
                        <option value="week">Esta semana</option>
                        <option value="month">Este mes</option>
                    </flux:select>

                    <flux:select wire:model.live="perPage" class="w-20">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </flux:select>

                    @if ($contextCustomerId === null && $canCreateNewSale)
                        <flux:button variant="primary" wire:click="togglePendingAndPartialSales"
                            class="bg-secondary-400! hover:bg-secondary-300!" size="sm">
                            <span class="hidden sm:inline">{{ $showPendingAndPartialSales ? __('Ocultar') : __('Mostrar') }} ventas pendientes y parciales</span>
                            <span class="sm:hidden">{{ $showPendingAndPartialSales ? __('Ocultar') : __('Mostrar') }} Pendientes/Parciales</span>
                        </flux:button>
                    @endif

                    @if ($canCreateNewSale)
                        <flux:button variant="primary" icon="plus" wire:click="openCreateModal" class="w-full xs:w-auto">
                            <span class="hidden sm:inline">{{ __('Nueva Venta') }}</span>
                            <span class="sm:hidden">{{ __('Nueva') }}</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Indicator -->
    @if ($dateFilter !== 'all' && $startDate && $endDate)
        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <flux:icon.calendar class="w-5 h-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <span class="font-medium">Filtro activo:</span>
                        @if ($dateFilter === 'today')
                            Ventas de hoy ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})
                        @elseif($dateFilter === 'week')
                            Ventas de esta semana ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} -
                            {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                        @elseif($dateFilter === 'month')
                            Ventas de este mes ({{ \Carbon\Carbon::parse($startDate)->format('M Y') }})
                        @endif
                    </p>
                </div>
                <div class="ml-auto">
                    <button wire:click="$set('dateFilter', 'all')"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Limpiar filtro
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('created_at')"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Fecha</span>
                                @if ($sortField === 'created_at')
                                    <flux:icon.chevron-up
                                        class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>

                        <th wire:click="sortBy('customer_id')"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Cliente</span>
                                @if ($sortField === 'customer_id')
                                    <flux:icon.chevron-up
                                        class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Productos
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reembolso
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>

                        <th wire:click="sortBy('user_id')"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Vendedor</span>
                                @if ($sortField === 'user_id')
                                    <flux:icon.chevron-up
                                        class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50 {{ $sale->trashed() ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                                @if ($sale->trashed())
                                    <div class="text-xs text-red-500 mt-1">Eliminada</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $sale->customer->name ?? 'Cliente eliminado' }}
                                </div>
                                @if ($sale->customer?->email)
                                    <div class="text-sm text-gray-500">
                                        {{ $sale->customer->email }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $sale->productList->count() }} producto(s)
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ number_format($sale->productList->sum('quantity'), 2) }} unidades
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                ${{ number_format($sale->productList->sum('total_price'), 2) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                @if ($sale->refund)

                                @else
                                    {{ __('Sin reembolso') }}
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'partial' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'paid' => 'Pagado',
                                        'partial' => 'Parcial',
                                        'cancelled' => 'Cancelado',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $statusColors[$sale->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$sale->payment_status] ?? $sale->payment_status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $sale->user->name ?? 'Usuario eliminado' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="openViewModal({{ $sale->id }})" icon="eye"
                                        aria-label="Ver venta" />

                                    @if (!$sale->trashed())
                                        <!-- Payment Actions (only for unpaid/partial sales) -->
                                        @if (in_array($sale->payment_status, ['pending', 'partial']))
                                            <flux:button size="sm" variant="ghost"
                                                wire:click="openAddPaymentModal({{ $sale->id }})"
                                                icon="credit-card" aria-label="Agregar pago"
                                                class="text-green-600 hover:text-green-700" />
                                        @endif

                                        <!-- Payment History (for all sales with payments) -->
                                        @if ($sale->payments && $sale->payments->count() > 0)
                                            <flux:button size="sm" variant="ghost"
                                                wire:click="openPaymentHistoryModal({{ $sale->id }})"
                                                icon="clock" aria-label="Historial de pagos"
                                                class="text-blue-600 hover:text-blue-700" />
                                        @endif

                                        <flux:button size="sm" variant="ghost"
                                            wire:click="openEditModal({{ $sale->id }})" icon="pencil"
                                            aria-label="Editar venta" />

                                        <flux:button size="sm" variant="ghost"
                                            wire:click="openDeleteModal({{ $sale->id }})" icon="trash"
                                            aria-label="Eliminar venta" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                @if ($search)
                                    No se encontraron ventas que coincidan con "{{ $search }}".
                                @else
                                    No hay ventas registradas.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($sales->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between text-sm text-gray-500">
        <div>
            Mostrando {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }}
            de {{ $sales->total() }} ventas
        </div>

        @if ($search)
            <button wire:click="$set('search', '')" class="text-primary-600 hover:text-primary-700">
                Limpiar búsqueda
            </button>
        @endif
    </div>

    <!-- Total Summary -->
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-blue-900">
                Total de ventas:
            </span>
            <span class="text-lg font-bold text-blue-900">
                ${{ number_format($totalAmount, 2) }}
            </span>
        </div>
    </div>

    <!-- Modals -->
    <livewire:sales.create-sale-modal :contextRouteId="$contextRouteId" :contextCustomerId="$contextCustomerId" />
    <livewire:sales.update-sale-modal :contextRouteId="$contextRouteId" :contextCustomerId="$contextCustomerId" />
    <livewire:sales.view-sale-modal />
    <livewire:sales.delete-sale-modal />
    <livewire:sales.payment-history-modal />
    <livewire:sales.add-payment-modal :contextRouteId="$contextRouteId" />

    <livewire:refunds.create-refund-modal />
</div>
