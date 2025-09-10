<flux:modal wire:model="showCreateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Nueva Venta') }}</flux:heading>
    </div>

    <div class="space-y-4">
        @if ($showCreateModal && session()->has('error'))
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
                        <button type="button" wire:click="clearErrorsForModal()"
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
        <div
            class="grid grid-cols-1 @if ($contextRouteId || $contextCustomerId) md:grid-cols-1 @else md:grid-cols-2 @endif gap-4">
            <!-- Customer Selection -->
            @if (!$contextCustomerId)
                <flux:field>
                    <flux:select wire:model="customer_id" label="{{ __('Cliente') }}" required>
                        <option value="">Seleccionar cliente...</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="customer_id" />
                </flux:field>
            @endif

            <!-- Route Selection -->
            @if (!$contextRouteId)
                <flux:field>
                    <flux:select wire:model="route_id" label="{{ __('Ruta') }}" required>
                        <option value="">Seleccionar ruta...</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->title }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="route_id" />
                </flux:field>
            @endif
        </div>

        <flux:separator class="my-6" />

        <livewire:sales.product-list-editor wire:model="saleProducts" />

        <!-- Payment Status -->
        <flux:field>
            <flux:select wire:model.live="payment_status" label="{{ __('Estado de Pago') }}">
                <option value="pending">Pendiente</option>
                <option value="paid">Pagado</option>
                <option value="partial">Pago Parcial</option>
            </flux:select>
            <flux:error name="payment_status" />
        </flux:field>

        <!-- Register box movement -->
        <flux:field>
            <flux:input wire:model="box_balance_delivered" label="{{ __('Cajas dejadas') }}" type="text"
                placeholder="" class="text-[var(--color-text)]!" />
            <flux:error name="box_balance_delivered" />
        </flux:field>

        <flux:field>
            <flux:input wire:model="box_balance_returned" label="{{ __('Cajas recogidas') }}" type="text"
                placeholder="" class="text-[var(--color-text)]!" />
            <flux:error name="box_balance_returned" />
        </flux:field>

        @if ($payment_status === 'partial')
            <flux:field>
                <flux:input wire:model="paid_amount" label="{{ __('Monto Pagado') }}" type="number" step="0.01"
                    min="0" placeholder="0.00" inputmode="decimal" pattern="[0-9]+(\.[0-9]{1,2})?"
                    x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                    x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                    class="text-[var(--color-text)]!" />
                <flux:error name="paid_amount" />
            </flux:field>
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


        <!-- Notes -->
        <flux:field class="mt-4">
            <flux:textarea wire:model="notes" label="{{ __('Notas') }}"
                placeholder="Notas adicionales sobre la venta..." rows="3" class="text-[var(--color-text)]!" />
            <flux:error name="notes" />
        </flux:field>


        <div class="flex justify-end gap-3 pt-4 flex-col sm:flex-row">
            <flux:button wire:click="$set('showCreateModal', false)" variant="outline" wire:loading.attr="disabled"
                wire:target="createSale" class="w-full sm:w-auto">
                {{ __('Cancelar') }}
            </flux:button>

            <flux:button wire:click="createSale" variant="primary" wire:loading.attr="disabled" wire:target="createSale"
                class="w-full sm:w-auto">
                <span wire:loading.remove wire:target="createSale">{{ __('Crear Venta') }}</span>
                <span wire:loading wire:target="createSale">Creando...</span>
            </flux:button>
        </div>
    </div>
</flux:modal>
