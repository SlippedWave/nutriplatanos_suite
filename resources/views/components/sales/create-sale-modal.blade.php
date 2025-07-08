<flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Nueva Venta') }}</flux:heading>
    </div>
    
    <div class="space-y-4">
        <div class="grid grid-cols-1 @if ($contextRouteId || $contextCustomerId) md:grid-cols-1 @else md:grid-cols-2
            
        @endif gap-4">
            <!-- Customer Selection -->
            @if (!$contextCustomerId)
            <flux:field>
                <flux:select wire:model="customer_id" label="{{ __('Cliente') }}" required>
                    <option value="">Seleccionar cliente...</option>
                    @foreach($customers as $customer)
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
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->title }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="route_id" />
            </flux:field>
            @endif
        </div>

        <flux:separator class="my-6" />

        <!-- Products Section -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="base">{{ __('Productos') }} *</flux:heading>
                <flux:button type="button" wire:click="addProduct" size="sm" variant="outline" icon="plus">
                    {{ __('Agregar Producto') }}
                </flux:button>
            </div>

            @foreach($saleProducts as $index => $product)
                <div class="bg-gray-50 p-4 rounded-lg mb-4" wire:key="product-{{ $index }}">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Producto {{ $index + 1 }}</span>
                        @if(count($saleProducts) > 1)
                            <flux:button 
                                type="button"
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
                        <flux:field>
                            <flux:select wire:model.live="saleProducts.{{ $index }}.product_id" label="{{ __('Producto') }}">
                                <option value="">Seleccionar...</option>
                                @foreach($products as $availableProduct)
                                    <option value="{{ $availableProduct->id }}">
                                        {{ $availableProduct->name }}  
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="saleProducts.{{ $index }}.product_id" />
                        </flux:field>

                        <!-- Quantity -->
                        <flux:field>
                            <flux:input 
                                wire:model.live="saleProducts.{{ $index }}.quantity"
                                label="{{ __('Cantidad') }}"
                                placeholder="0.00"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                pattern="[0-9]+(\.[0-9]{1,2})?"
                                x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                                x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                class="text-[var(--color-text)]!"
                            />
                            <flux:error name="saleProducts.{{ $index }}.quantity" />
                        </flux:field>

                        <!-- Price per Unit -->
                        <flux:field>
                            <flux:input 
                                wire:model.live="saleProducts.{{ $index }}.price_per_unit"
                                label="{{ __('Precio Unitario') }}"
                                placeholder="0.00"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                pattern="[0-9]+(\.[0-9]{1,2})?"
                                x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                                x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                class="text-[var(--color-text)]!"
                            />
                            <flux:error name="saleProducts.{{ $index }}.price_per_unit" />
                        </flux:field>
                    </div>

                    @if(is_numeric($product['quantity']) && is_numeric($product['price_per_unit']) && $product['quantity'] > 0 && $product['price_per_unit'] > 0)
                        <div class="mt-3 text-right">
                            <span class="text-sm font-medium text-gray-900">
                                Subtotal: ${{ number_format((float)$product['quantity'] * (float)$product['price_per_unit'], 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Total -->
            @php
                $total = collect($saleProducts)->sum(function($product) {
                    $quantity = (float) ($product['quantity'] ?? 0);
                    $pricePerUnit = (float) ($product['price_per_unit'] ?? 0);
                    return $quantity * $pricePerUnit;
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

        <!-- Payment Status -->
        <flux:field>
            <flux:select wire:model.live="payment_status" label="{{ __('Estado de Pago') }}">
                <option value="pending">Pendiente</option>
                <option value="paid">Pagado</option>
                <option value="partial">Pago Parcial</option>
            </flux:select>
            <flux:error name="payment_status" />
        </flux:field>

        @if ($payment_status === 'partial')
            <flux:field>
            <flux:input 
                wire:model="paid_amount" 
                label="{{ __('Monto Pagado') }}" 
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                inputmode="decimal"
                pattern="[0-9]+(\.[0-9]{1,2})?"
                x-on:keypress="$event.charCode >= 48 && $event.charCode <= 57 || $event.charCode === 46"
                x-on:input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                class="text-[var(--color-text)]!"
            />
            <flux:error name="paid_amount" />
            </flux:field>
        @endif

        <!-- Notes -->
        <flux:field class="mt-4">
            <flux:textarea 
                wire:model="notes" 
                label="{{ __('Notas') }}"
                placeholder="Notas adicionales sobre la venta..."
                rows="3"
                class="text-[var(--color-text)]!"
            />
            <flux:error name="notes" />
        </flux:field>
        

        <div class="flex justify-end gap-3 pt-4">
            <flux:button wire:click="closeModals" variant="outline">
                {{ __('Cancelar') }}
            </flux:button>
            
            <flux:button wire:click="createSale" variant="primary">
                {{ __('Crear Venta') }}
            </flux:button>
        </div>
    </div>
</flux:modal>