<flux:modal wire:model="showViewModal" class="space-y-4 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Detalles de la Venta') }}</flux:heading>
    </div>

    @if($selectedSale)
        <div class="space-y-6">
            <!-- Sale Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Fecha de Venta:</span>
                        <p class="text-sm text-gray-900">{{ $selectedSale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500">Cliente:</span>
                        <p class="text-sm text-gray-900">{{ $selectedSale->customer->name ?? 'Cliente eliminado' }}</p>
                        @if($selectedSale->customer?->email)
                            <p class="text-xs text-gray-500">{{ $selectedSale->customer->email }}</p>
                        @endif
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500">Ruta:</span>
                        <p class="text-sm text-gray-900">{{ $selectedSale->route->title ?? 'Ruta eliminada' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500">Vendedor:</span>
                        <p class="text-sm text-gray-900">{{ $selectedSale->user->name ?? 'Usuario eliminado' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500">Estado de Pago:</span>
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
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$selectedSale->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$selectedSale->payment_status] ?? $selectedSale->payment_status }}
                            </span>
                        </div>
                    </div>

                    @if($selectedSale->trashed())
                        <div>
                            <span class="text-sm font-medium text-gray-500">Estado:</span>
                            <div class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Eliminada
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Products Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Productos</h3>
                
                @if($selectedSale->saleDetails && $selectedSale->saleDetails->count() > 0)
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Precio Unitario
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedSale->saleDetails as $detail)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $detail->product->name ?? 'Producto eliminado' }}
                                            </div>
                                            @if($detail->product?->description)
                                                <div class="text-sm text-gray-500">
                                                    {{ $detail->product->description }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ number_format($detail->quantity, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            ${{ number_format($detail->price_per_unit, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                            ${{ number_format($detail->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        Total de la Venta:
                                    </td>
                                    <td class="px-6 py-3 text-center text-sm font-bold text-gray-900">
                                        ${{ number_format($selectedSale->saleDetails->sum('total_price'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <flux:icon.shopping-bag class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Sin productos</h3>
                        <p class="mt-1 text-sm text-gray-500">Esta venta no tiene productos asociados.</p>
                    </div>
                @endif
            </div>

            <!-- Summary -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-900">
                            {{ $selectedSale->saleDetails->count() }}
                        </div>
                        <div class="text-sm text-blue-700">Productos</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">
                            {{ number_format($selectedSale->saleDetails->sum('quantity'), 2) }}
                        </div>
                        <div class="text-sm text-blue-700">Total Unidades</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">
                            ${{ number_format($selectedSale->saleDetails->sum('total_price'), 2) }}
                        </div>
                        <div class="text-sm text-blue-700">Total Venta</div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            @if($selectedSale->notes()->count() > 0)
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
                    <div class="space-y-3">
                        @foreach($selectedSale->notes()->latest()->get() as $note)
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm text-gray-900">{{ $note->content }}</p>
                                    <span class="text-xs text-gray-500 ml-2">
                                        {{ $note->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                @if($note->user)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Por: {{ $note->user->name }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="flex justify-end gap-3 pt-4">
        <flux:button wire:click="closeModals" variant="primary">
            {{ __('Cerrar') }}
        </flux:button>
    </div>
</flux:modal>
