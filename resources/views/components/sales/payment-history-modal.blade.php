<flux:modal wire:model="showPaymentHistoryModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Historial de Pagos') }}</flux:heading>
    </div>
    
    <div class="space-y-4">
        @if ($showPaymentHistoryModal && session()->has('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800">
                            Error
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <button 
                            type="button" 
                            wire:click="clearErrorsForModal('payment_history')"
                            class="text-red-400 hover:text-red-600"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($selectedSale)
            <!-- Sale Information Summary -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Información de la Venta</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Cliente:</span>
                        <span class="text-blue-900 font-medium">{{ $selectedSale->customer->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Fecha:</span>
                        <span class="text-blue-900 font-medium">{{ $selectedSale->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Total:</span>
                        <span class="text-blue-900 font-medium">${{ number_format($selectedSale->saleDetails->sum('total_price'), 2) }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Total Pagado:</span>
                        <span class="text-blue-900 font-medium">${{ number_format($selectedSale->total_paid, 2) }}</span>
                    </div>
                    <div class="col-span-2">
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
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$selectedSale->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$selectedSale->payment_status] ?? $selectedSale->payment_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            @if($selectedSale->payments && $selectedSale->payments->count() > 0)
                <div class="space-y-3">
                    <h3 class="text-lg font-medium text-gray-900">Historial de Pagos</h3>
                    
                    <div class="bg-white border border-gray-200 rounded-lg overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Monto
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Método
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ruta
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedSale->payments->sortByDesc('payment_date') as $payment)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->payment_method_label }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->route->title ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @if($payment->notes)
                                        <tr class="bg-gray-50">
                                            <td colspan="5" class="px-4 py-2 text-sm text-gray-600">
                                                <strong>Notas:</strong> {{ $payment->notes }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <span class="text-sm text-gray-600">Total de Pagos:</span>
                            <span class="block text-lg font-semibold text-gray-900">
                                ${{ number_format($selectedSale->payments->sum('amount'), 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Saldo Pendiente:</span>
                            <span class="block text-lg font-semibold 
                                @if($selectedSale->remaining_balance > 0) text-red-600 @else text-green-600 @endif">
                                ${{ number_format($selectedSale->remaining_balance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin pagos registrados</h3>
                    <p class="mt-1 text-sm text-gray-500">Esta venta aún no tiene pagos registrados.</p>
                    
                    @if(in_array($selectedSale->payment_status, ['pending', 'partial']))
                        <div class="mt-4">
                            <flux:button 
                                wire:click="$toggle('showPaymentHistoryModal'); $toggle('showAddPaymentModal')"
                                variant="primary"
                                size="sm"
                            >
                                Agregar Primer Pago
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <div class="flex justify-end gap-3 pt-4">
            <flux:button 
                wire:click="closeModals"
                variant="outline"
            >
                {{ __('Cerrar') }}
            </flux:button>
            
            @if($selectedSale && in_array($selectedSale->payment_status, ['pending', 'partial']))
                <flux:button 
                    wire:click="$toggle('showPaymentHistoryModal'); $toggle('showAddPaymentModal')"
                    variant="primary"
                >
                    {{ __('Agregar Pago') }}
                </flux:button>
            @endif
        </div>
    </div>
</flux:modal>
