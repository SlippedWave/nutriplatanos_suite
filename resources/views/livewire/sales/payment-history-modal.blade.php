<flux:modal wire:model="showPaymentHistoryModal"
    class="space-y-6 mx-auto border-0 bg-background! w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="text-base sm:text-lg">{{ __('Historial de Pagos') }}</flux:heading>
    </div>

    <div class="space-y-4">
        @if ($selectedSale)
            <!-- Sale Information Summary -->
            <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Información de la Venta</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Cliente:</span>
                        <span
                            class="text-blue-900 font-medium break-words">{{ $selectedSale->customer->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Fecha:</span>
                        <span class="text-blue-900 font-medium">{{ $selectedSale->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Total:</span>
                        <span
                            class="text-blue-900 font-medium">${{ number_format($selectedSale->productList->sum('total_price'), 2) }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Total Pagado:</span>
                        <span
                            class="text-blue-900 font-medium">${{ number_format($selectedSale->total_paid, 2) }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-blue-700">Estado de Pago:</span>
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
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$selectedSale->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$selectedSale->payment_status] ?? $selectedSale->payment_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            @if ($selectedSale->payments && $selectedSale->payments->count() > 0)
                <div class="space-y-3">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900">Historial de Pagos</h3>

                    <!-- Mobile cards -->
                    <div class="space-y-2 md:hidden">
                        @foreach ($selectedSale->payments->sortByDesc('payment_date') as $payment)
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            ${{ number_format($payment->amount, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-700 mt-1">
                                            {{ $payment->payment_method_label }}
                                        </div>
                                        <div class="text-[11px] text-gray-600 mt-1">
                                            Usuario: {{ $payment->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-[11px] text-gray-600">
                                            Ruta: {{ $payment->route->title ?? 'N/A' }}
                                        </div>
                                        @if ($payment->notes)
                                            <div class="text-xs text-gray-600 mt-2 break-words">
                                                <strong>Notas:</strong> {{ $payment->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Table for md+ -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th
                                        class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Monto
                                    </th>
                                    <th
                                        class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                                        Método
                                    </th>
                                    <th
                                        class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Usuario
                                    </th>
                                    <th
                                        class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Ruta
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($selectedSale->payments->sortByDesc('payment_date') as $payment)
                                    <tr>
                                        <td
                                            class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </td>
                                        <td
                                            class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                                            ${{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td
                                            class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                            {{ $payment->payment_method_label }}
                                        </td>
                                        <td
                                            class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden md:table-cell">
                                            {{ $payment->user->name ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden lg:table-cell">
                                            {{ $payment->route->title ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @if ($payment->notes)
                                        <tr class="bg-gray-50">
                                            <td colspan="5"
                                                class="px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-600">
                                                <strong>Notas:</strong> {{ $payment->notes }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Summary -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <div>
                            <span class="text-sm text-gray-600">Total de Pagos:</span>
                            <span class="block text-lg font-semibold text-gray-900">
                                ${{ number_format($selectedSale->payments->sum('amount'), 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Saldo Pendiente:</span>
                            <span
                                class="block text-lg font-semibold 
                                @if ($selectedSale->remaining_balance > 0) text-red-600 @else text-green-600 @endif">
                                ${{ number_format($selectedSale->remaining_balance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 px-3">
                    <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin pagos registrados</h3>
                    <p class="mt-1 text-sm text-gray-500">Esta venta aún no tiene pagos registrados.</p>

                    @if (in_array($selectedSale->payment_status, ['pending', 'partial']))
                        <div class="mt-4">
                            <flux:button wire:click="openAddPaymentModal" variant="primary" size="sm">
                                Agregar Primer Pago
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-2 sm:pt-4">
            <flux:button wire:click="$set('showPaymentHistoryModal', false)" variant="outline" class="w-full sm:w-auto">
                {{ __('Cerrar') }}
            </flux:button>

            @if ($selectedSale && in_array($selectedSale->payment_status, ['pending', 'partial']))
                <flux:button wire:click="openAddPaymentModal" variant="primary" class="w-full sm:w-auto">
                    {{ __('Agregar Pago') }}
                </flux:button>
            @endif
        </div>
    </div>
</flux:modal>
