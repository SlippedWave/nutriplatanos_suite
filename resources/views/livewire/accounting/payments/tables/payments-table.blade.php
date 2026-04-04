<div>
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
                        <th wire:click="sortBy('route_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Ruta</span>
                                @if($sortField === 'route_id')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('user_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Usuario</span>
                                @if($sortField === 'user_id')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Cliente</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Método de pago
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $payment->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ optional($payment->route)->title ?? 'Ruta eliminada' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ optional($payment->user)->name ?? 'Usuario eliminado' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ optional(optional($payment->sale)->customer)->name ?? 'Cliente eliminado' }}
                                <div class="text-xs text-gray-500">
                                    {{ 'Para venta con ID: \'' . (optional($payment->sale)->id ?? 'N/A') . '\'' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                ${{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $payment->getPaymentMethodLabelAttribute() ?? 'Método desconocido' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay pagos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <div class="flex items-center justify-between text-sm text-gray-500 mt-2">
        <div>
            Mostrando {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }} 
            de {{ $payments->total() }} pagos
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg mt-2">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-blue-900">
                Total de pagos:
            </span>
            <span class="text-lg font-bold text-blue-900">
                ${{ number_format($totalAmount, 2) }}
            </span>
        </div>
    </div>
</div>