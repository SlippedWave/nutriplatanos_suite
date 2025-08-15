<flux:modal wire:model="showDeleteSaleModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Eliminar Venta') }}</flux:heading>
    </div>
    
    @if($selectedSale)
        <div class="space-y-4">
            <div class="bg-danger-50 border border-danger-800 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <flux:icon.exclamation-triangle class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Confirmar eliminación
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>
                                ¿Estás seguro de que deseas eliminar esta venta? Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Details -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Detalles de la venta a eliminar:</h4>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha:</span>
                        <span class="text-gray-900">{{ $selectedSale->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cliente:</span>
                        <span class="text-gray-900">{{ $selectedSale->customer->name ?? 'Cliente eliminado' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Productos:</span>
                        <span class="text-gray-900">{{ $selectedSale->saleDetails->count() }} producto(s)</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total:</span>
                        <span class="text-gray-900 font-medium">
                            ${{ number_format($selectedSale->saleDetails->sum('total_price'), 2) }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Estado de Pago:</span>
                        <span class="text-gray-900">
                            @php
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'paid' => 'Pagado',
                                    'partial' => 'Parcial',
                                    'cancelled' => 'Cancelado',
                                ];
                            @endphp
                            {{ $statusLabels[$selectedSale->payment_status] ?? $selectedSale->payment_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Products List -->
            @if($selectedSale->saleDetails && $selectedSale->saleDetails->count() > 0)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Productos incluidos:</h4>
                    <div class="space-y-1">
                        @foreach($selectedSale->saleDetails as $detail)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">
                                    {{ $detail->product->name ?? 'Producto eliminado' }} 
                                    ({{ number_format($detail->quantity, 2) }} unidades)
                                </span>
                                <span class="text-gray-900">
                                    ${{ number_format($detail->total_price, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <flux:icon.information-circle class="h-5 w-5 text-yellow-400" />
                    </div>
                    <div class="ml-3">
                        <div class="text-sm text-yellow-700">
                            <p>
                                <strong>Nota:</strong> La venta será marcada como eliminada pero se mantendrá en el sistema para auditoría. 
                                Los datos de los productos y totales se conservarán para reportes históricos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
        <flux:button wire:click="closeModals" variant="outline" class="w-full sm:w-auto">
            {{ __('Cancelar') }}
        </flux:button>
        
        <flux:button wire:click="deleteSale" variant="danger" class="w-full sm:w-auto">
            {{ __('Eliminar Venta') }}
        </flux:button>
    </div>
</flux:modal>
