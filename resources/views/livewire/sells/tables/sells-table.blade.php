<div class="space-y-4">
    <!-- Search and Controls -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar clientes..."
                type="search"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>
        
        <div class="flex gap-2">
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

    <!-- Date Range Indicator -->
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
                        Ventas de hoy ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'week')
                        Ventas de esta semana ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                    @elseif($dateFilter === 'month')
                        Ventas de este mes ({{ \Carbon\Carbon::parse($startDate)->format('M Y') }})
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

    <!-- Sales Table -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('created_at')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1 justify-center">
                                <span>Fecha de Venta</span>
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('customer_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1 justify-center">
                                <span>Cliente</span>
                                @if($sortField === 'customer_id')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('user_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1 justify-center">
                                <span>Vendedor</span>
                                @if($sortField === 'user_id')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Peso (kg)
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Precio por kg
                        </th>
                        
                        <th wire:click="sortBy('total_amount')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center space-x-1 justify-center">
                                <span>Monto Total</span>
                                @if($sortField === 'total_amount')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha de Registro
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $sale->customer->name }}
                                </div>
                                @if($sale->customer->email)
                                    <div class="text-sm text-gray-500">
                                        {{ $sale->customer->email }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $sale->user->name }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ number_format($sale->weight_kg, 3) }} kg
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                ${{ number_format($sale->price_per_kg, 2) }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                ${{ number_format($sale->total_amount, 2) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
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
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$sale->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$sale->payment_status] ?? $sale->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                @if($search)
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
    </div>

    <!-- Pagination and Summary -->
    <div class="flex justify-between items-center">
        <div class="text-sm text-gray-700">
            <span class="font-medium">{{ $sales->firstItem() ?: 0 }}</span>
            -
            <span class="font-medium">{{ $sales->lastItem() ?: 0 }}</span>
            de
            <span class="font-medium">{{ $sales->total() }}</span>
            resultados
        </div>
        
        {{ $sales->links() }}
    </div>

    <!-- Total Summary -->
    <div class="bg-blue-50 p-4 rounded-lg">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-blue-900">
                Total de ventas mostradas:
            </span>
            <span class="text-lg font-bold text-blue-900">
                ${{ number_format($totalAmount, 2) }}
            </span>
        </div>
    </div>
</div>
