<flux:modal wire:model="showAddPaymentModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Agregar Pago') }}</flux:heading>
    </div>

    <div class="space-y-4">

        @if ($selectedSale)
            <!-- Sale Information Summary -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Información de la Venta</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
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
                        <span class="text-blue-700">Pagado:</span>
                        <span
                            class="text-blue-900 font-medium">${{ number_format($selectedSale->total_paid, 2) }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-blue-700">Saldo Pendiente:</span>
                        <span
                            class="text-blue-900 font-bold text-lg">${{ number_format($selectedSale->remaining_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Payment Amount -->
                <flux:field>
                    <flux:input wire:model="amount" label="{{ __('Monto del Pago') }}" placeholder="0.00"
                        type="number" step="0.01" min="0.01" max="{{ $selectedSale->remaining_balance }}"
                        inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?"
                        x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                        x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                        class="text-[var(--color-text)]!" required />
                </flux:field>

                <!-- Payment Date -->
                <flux:field>
                    <flux:input wire:model="payment_date" label="{{ __('Fecha de Pago') }}" type="date"
                        max="{{ now()->toDateString() }}" required />
                </flux:field>

                <!-- Payment Method -->
                <flux:field>
                    <flux:select wire:model="payment_method" label="{{ __('Método de Pago') }}" required>
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <!-- Route Selection -->
                <flux:field>
                    <flux:select wire:model="payment_route_id" label="{{ __('Ruta') }}">
                        <option value="">Seleccionar ruta...</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->title }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <!-- Payment Notes -->
            <flux:field>
                <flux:textarea wire:model="payment_notes" label="{{ __('Notas del Pago') }}"
                    placeholder="Notas adicionales sobre el pago..." rows="3" class="text-[var(--color-text)]!" />
            </flux:field>

            <!-- Quick Payment Buttons -->
            <div class="flex flex-wrap gap-2">
                <flux:button type="button" wire:click="$set('amount', {{ $selectedSale->remaining_balance }})"
                    size="sm" variant="outline">
                    Pago Completo (${{ number_format($selectedSale->remaining_balance, 2) }})
                </flux:button>

                @if ($selectedSale->remaining_balance >= 100)
                    <flux:button type="button" wire:click="$set('amount', 100)" size="sm"
                        variant="outline">
                        $100
                    </flux:button>
                @endif

                @if ($selectedSale->remaining_balance >= 50)
                    <flux:button type="button" wire:click="$set('amount', 50)" size="sm"
                        variant="outline">
                        $50
                    </flux:button>
                @endif
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-2 sm:pt-4">
            <flux:button wire:click="$set('showAddPaymentModal', false)" variant="outline" wire:loading.attr="disabled"
                wire:target="addPayment" class="w-full sm:w-auto">
                {{ __('Cancelar') }}
            </flux:button>

            <flux:button wire:click="addPayment" variant="primary" wire:loading.attr="disabled" wire:target="addPayment"
                class="w-full sm:w-auto">
                <span wire:loading.remove wire:target="addPayment">{{ __('Agregar Pago') }}</span>
                <span wire:loading wire:target="addPayment">Procesando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
