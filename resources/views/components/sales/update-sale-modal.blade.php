<flux:modal wire:model="showUpdateModal" class="space-y-4 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Actualizar Venta') }}</flux:heading>
    </div>
    
    @if($selectedSale)
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Customer Selection -->
                <div>
                    <flux:label for="customer_id">{{ __('Cliente') }} *</flux:label>
                    <flux:select wire:model="customer_id" name="customer_id" required>
                        <option value="">Seleccionar cliente...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('customer_id') <flux:error>{{ $message }}</flux:error> @enderror
                </div>

                <!-- Route Selection -->
                <div>
                    <flux:label for="route_id">{{ __('Ruta') }} *</flux:label>
                    <flux:select wire:model="route_id" name="route_id" required>
                        <option value="">Seleccionar ruta...</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->title }}</option>
                        @endforeach
                    </flux:select>
                    @error('route_id') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
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
                @error('payment_status') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            @if ($payment_status === 'partial')
                <div>
                    <flux:label for="paid_amount">{{ __('Monto Pagado') }}</flux:label>
                    <flux:input 
                        wire:model="paid_amount" 
                        name="paid_amount"
                        type="number" 
                        step="0.01" 
                        min="0"
                        placeholder="0.00"
                        inputmode="decimal"
                        pattern="[0-9]+(\.[0-9]{1,2})?"
                        x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                        x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                    />
                    @error('paid_amount') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            @endif

            <!-- Products Section -->
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:label class="text-lg font-medium">{{ __('Productos') }} *</flux:label>
                    <flux:button wire:click="addProduct" size="sm" variant="outline" icon="plus">
                        {{ __('Agregar Producto') }}
                    </flux:button>
                </div>

                @foreach($saleProducts as $index => $product)
                    <div class="bg-gray-50 p-4 rounded-lg mb-4" wire:key="product-{{ $index }}">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">Producto {{ $index + 1 }}</span>
                            @if(count($saleProducts) > 1)
                                <flux:button 
                                    wire:click="removeProduct({{ $index }})" 
                                    size="sm" 
                                    variant="ghost" 
                                    icon="x-mark"
                                    class="text-red-600 hover:text-red-700"
                                />
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Product Selection -->
                            <div>
                                <flux:label for="saleProducts.{{ $index }}.product_id">{{ __('Producto') }}</flux:label>
                                <flux:select wire:model="saleProducts.{{ $index }}.product_id" name="saleProducts.{{ $index }}.product_id">
                                    <option value="">Seleccionar...</option>
                                    @foreach($products as $availableProduct)
                                        <option value="{{ $availableProduct->id }}">
                                            {{ $availableProduct->name }} - ${{ number_format($availableProduct->price, 2) }}
                                        </option>
                                    @endforeach
                                </flux:select>
                                @error('saleProducts.' . $index . '.product_id') <flux:error>{{ $message }}</flux:error> @enderror
                            </div>

                            <!-- Quantity -->
                            <div>
                                <flux:label for="saleProducts.{{ $index }}.quantity">{{ __('Cantidad') }}</flux:label>
                                <flux:input 
                                    wire:model="saleProducts.{{ $index }}.quantity"
                                    name="saleProducts.{{ $index }}.quantity"
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                    pattern="[0-9]+(\.[0-9]{1,2})?"
                                    x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                                    x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                />
                                @error('saleProducts.' . $index . '.quantity') <flux:error>{{ $message }}</flux:error> @enderror
                            </div>

                            <!-- Price per Unit -->
                            <div>
                                <flux:label for="saleProducts.{{ $index }}.price_per_unit">{{ __('Precio Unitario') }}</flux:label>
                                <flux:input 
                                    wire:model="saleProducts.{{ $index }}.price_per_unit"
                                    name="saleProducts.{{ $index }}.price_per_unit"
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                    pattern="[0-9]+(\.[0-9]{1,2})?"
                                    x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                                    x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                />
                                @error('saleProducts.' . $index . '.price_per_unit') <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                        </div>

                        @if($product['quantity'] && $product['price_per_unit'])
                            <div class="mt-3 text-right">
                                <span class="text-sm font-medium text-gray-900">
                                    Subtotal: ${{ number_format($product['quantity'] * $product['price_per_unit'], 2) }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Total -->
                @php
                    $total = collect($saleProducts)->sum(function($product) {
                        return ($product['quantity'] ?? 0) * ($product['price_per_unit'] ?? 0);
                    });
                @endphp
                @if($total > 0)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-blue-900">Total de la Venta:</span>
                            <span class="text-xl font-bold text-blue-900">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Notes -->
            <div>
                <flux:label for="notes">{{ __('Notas') }}</flux:label>
                <flux:textarea 
                    wire:model="notes" 
                    name="notes"
                    placeholder="Notas adicionales sobre la venta..."
                    rows="3"
                />
                @error('notes') <flux:error>{{ $message }}</flux:error> @enderror
            </div>
        </div>
    @endif

    <div class="flex justify-end gap-3 pt-4">
        <flux:button wire:click="closeModals" variant="outline">
            {{ __('Cancelar') }}
        </flux:button>
        
        <flux:button wire:click="updateSale" variant="primary">
            {{ __('Actualizar Venta') }}
        </flux:button>
    </div>
</flux:modal>
