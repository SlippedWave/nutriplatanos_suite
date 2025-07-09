<flux:modal wire:model="showAddPaymentModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Agregar Pago') }}</flux:heading>
    </div>
    
    <div class="space-y-4">
        @if ($showAddPaymentModal && session()->has('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800">
                            Error al procesar el pago
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <button 
                            type="button" 
                            wire:click="clearErrorsForModal('payment')"
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
                        <span class="text-blue-700">Pagado:</span>
                        <span class="text-blue-900 font-medium">${{ number_format($selectedSale->total_paid, 2) }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-blue-700">Saldo Pendiente:</span>
                        <span class="text-blue-900 font-bold text-lg">${{ number_format($selectedSale->remaining_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Payment Amount -->
                <flux:field>
                    <flux:input 
                        wire:model="payment_amount"
                        label="{{ __('Monto del Pago') }}"
                        placeholder="0.00"
                        type="number"
                        step="0.01"
                        min="0.01"
                        max="{{ $selectedSale->remaining_balance }}"
                        inputmode="decimal"
                        pattern="[0-9]+(\.[0-9]{1,2})?"
                        x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                        x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                        class="text-[var(--color-text)]!"
                        required
                    />
                    <flux:error name="payment_amount" />
                </flux:field>

                <!-- Payment Date -->
                <flux:field>
                    <flux:input 
                        wire:model="payment_date"
                        label="{{ __('Fecha de Pago') }}"
                        type="date"
                        max="{{ now()->toDateString() }}"
                        required
                    />
                    <flux:error name="payment_date" />
                </flux:field>

                <!-- Payment Method -->
                <flux:field>
                    <flux:select wire:model="payment_method" label="{{ __('Método de Pago') }}" required>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_method" />
                </flux:field>

                <!-- Route Selection -->
                <flux:field>
                    <flux:select wire:model="payment_route_id" label="{{ __('Ruta') }}">
                        <option value="">Seleccionar ruta...</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->title }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_route_id" />
                </flux:field>
            </div>

            <!-- Payment Notes -->
            <flux:field>
                <flux:textarea 
                    wire:model="payment_notes"
                    label="{{ __('Notas del Pago') }}"
                    placeholder="Notas adicionales sobre el pago..."
                    rows="3"
                    class="text-[var(--color-text)]!"
                />
                <flux:error name="payment_notes" />
            </flux:field>

            <!-- Quick Payment Buttons -->
            <div class="flex gap-2">
                <flux:button 
                    type="button"
                    wire:click="$set('payment_amount', {{ $selectedSale->remaining_balance }})"
                    size="sm"
                    variant="outline"
                >
                    Pago Completo (${{ number_format($selectedSale->remaining_balance, 2) }})
                </flux:button>
                
                @if($selectedSale->remaining_balance >= 100)
                    <flux:button 
                        type="button"
                        wire:click="$set('payment_amount', 100)"
                        size="sm"
                        variant="outline"
                    >
                        $100
                    </flux:button>
                @endif
                
                @if($selectedSale->remaining_balance >= 50)
                    <flux:button 
                        type="button"
                        wire:click="$set('payment_amount', 50)"
                        size="sm"
                        variant="outline"
                    >
                        $50
                    </flux:button>
                @endif
            </div>
        @endif

        <div class="flex justify-end gap-3 pt-4">
            <flux:button 
                wire:click="closeModals"
                variant="outline"
                wire:loading.attr="disabled"
                wire:target="addPayment"
            >
                {{ __('Cancelar') }}
            </flux:button>
            
            <flux:button 
                wire:click="addPayment"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="addPayment"
            >
                <span wire:loading.remove wire:target="addPayment">{{ __('Agregar Pago') }}</span>
                <span wire:loading wire:target="addPayment">Procesando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
