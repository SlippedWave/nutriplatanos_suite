<flux:modal wire:model="showUpdateModal"
    class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Actualizar Venta') }}</flux:heading>
    </div>

    @if ($showUpdateModal && session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800">
                        Error al procesar la venta
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <button type="button" wire:click="clearErrorsForModal('update')"
                        class="text-red-400 hover:text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($selectedSale)
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer Selection -->
                @if (!$contextCustomerId)
                    <div>
                        <flux:label for="customer_id">{{ __('Cliente') }} *</flux:label>
                        <flux:select wire:model="customer_id" name="customer_id" required>
                            <option value="">Seleccionar cliente...</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('customer_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>
                @endif

                @if (!$contextRouteId)
                    <div>
                        <flux:label for="route_id">{{ __('Ruta') }} *</flux:label>
                        <flux:select wire:model="route_id" name="route_id" required>
                            <option value="">Seleccionar ruta...</option>
                            @foreach ($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->title }}</option>
                            @endforeach
                        </flux:select>
                        @error('route_id')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Payment Status -->
            <div>
                <flux:label for="payment_status">{{ __('Estado de Pago') }}</flux:label>
                <flux:select wire:model.live="payment_status" name="payment_status">
                    <option value="pending">Pendiente</option>
                    <option value="paid">Pagado</option>
                    <option value="partial">Pago Parcial</option>
                    <option value="cancelled">Cancelado</option>
                </flux:select>
                @error('payment_status')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            @if ($payment_status === 'partial')
                <div>
                    <flux:label for="paid_amount">{{ __('Monto Pagado') }}</flux:label>
                    <flux:input wire:model="paid_amount" name="paid_amount" type="number" step="0.01" min="0"
                        placeholder="0.00" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?"
                        x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                        x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')" />
                    @error('paid_amount')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>
            @endif

            @if ($payment_status === 'paid' || $payment_status === 'partial')
                <flux:field>
                    <flux:select wire:model="payment_method" label="{{ __('Método de Pago') }}" required>
                        <option value="">Seleccionar método de pago...</option>
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_method" />
                </flux:field>
            @endif

            <!-- Products Section -->
            <livewire:sales.product-details-editor wire:model="saleProducts" />

            <!-- Notes -->
            <div>
                <flux:label for="notes">{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" name="notes" placeholder="Notas adicionales sobre la venta..."
                    rows="3" />
                @error('notes')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>
        </div>
    @endif

    <div class="flex justify-end gap-3 pt-4 flex-col sm:flex-row">
        <flux:button wire:click="$set('showUpdateModal', false)" variant="outline" wire:loading.attr="disabled"
            wire:target="updateSale" class="w-full sm:w-auto">
            {{ __('Cancelar') }}
        </flux:button>

        <flux:button wire:click="updateSale" variant="primary" wire:loading.attr="disabled" wire:target="updateSale"
            class="w-full sm:w-auto">
            <span wire:loading.remove wire:target="updateSale">{{ __('Actualizar Venta') }}</span>
            <span wire:loading wire:target="updateSale">Actualizando...</span>
        </flux:button>
    </div>
</flux:modal>
