<div>
    @if ($refund)
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Reembolso</h3>
                <div class="flex gap-2">
                    <flux:button size="sm" variant="ghost" icon="pencil"
                        wire:click="$dispatch('open-update-refund-modal', { refund_id: {{ $refund->id }} })"
                        class="text-blue-600 hover:text-blue-700">
                        {{ __('Editar') }}
                    </flux:button>
                    <flux:button size="sm" variant="ghost" icon="trash"
                        wire:click="$dispatch('open-delete-refund-modal', { refund_id: {{ $refund->id }} })"
                        class="text-red-600 hover:text-red-700">
                        {{ __('Eliminar') }}
                    </flux:button>
                </div>
            </div>

            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-orange-900">Método</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                        {{ \App\Models\Refund::REFUND_METHODS[$refund->refund_method] ?? $refund->refund_method }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-orange-900">Monto reembolsado</span>
                    <span class="text-sm font-bold text-orange-900">
                        ${{ number_format($refund->refunded_amount, 2) }}
                    </span>
                </div>

                <div>
                    <span class="text-sm font-medium text-orange-900">Razón</span>
                    <p class="text-sm text-orange-800 mt-1">{{ $refund->reason }}</p>
                </div>

                @if ($refund->productList->count() > 0)
                    <div>
                        <span class="text-sm font-medium text-orange-900">Productos devueltos</span>
                        <div class="mt-2 overflow-auto rounded-lg ring-1 ring-orange-200">
                            <table class="min-w-full divide-y divide-orange-200 text-sm">
                                <thead class="bg-orange-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-orange-900">Producto</th>
                                        <th class="px-4 py-2 text-center font-medium text-orange-900">Cantidad</th>
                                        <th class="px-4 py-2 text-center font-medium text-orange-900">Precio</th>
                                        <th class="px-4 py-2 text-center font-medium text-orange-900">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-orange-100 bg-white">
                                    @foreach ($refund->productList as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-900">{{ $item->product->name ?? 'Producto eliminado' }}</td>
                                            <td class="px-4 py-2 text-center text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="px-4 py-2 text-center text-gray-900">${{ number_format($item->price_per_unit, 2) }}</td>
                                            <td class="px-4 py-2 text-center font-medium text-gray-900">${{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @elseif ($allowCreate)
        <div class="flex justify-end">
            <flux:button size="sm" variant="ghost" icon="arrow-uturn-left"
                wire:click="$dispatch('open-create-refund-modal', { sale_id: {{ $sale_id }} })"
                class="text-orange-600 hover:text-orange-700">
                {{ __('Agregar reembolso') }}
            </flux:button>
        </div>
    @endif
</div>
