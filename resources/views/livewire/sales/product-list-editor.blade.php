<div>
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="base">{{ __('Productos') }} *</flux:heading>
        <flux:button type="button" wire:click="addProduct" size="sm" variant="outline" icon="plus">
            {{ __('Agregar Producto') }}
        </flux:button>
    </div>

    @foreach ($saleProducts as $index => $product)
        <div class="bg-gray-50 p-4 rounded-lg mb-4" wire:key="product-{{ $index }}">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">Producto {{ $index + 1 }}</span>
                @if (count($saleProducts) > 1)
                    <flux:button type="button" wire:click="removeProduct({{ $index }})" size="sm"
                        variant="ghost" icon="x-mark" class="text-red-600 hover:text-red-700" />
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Product Selection -->
                <flux:field>
                    <flux:select wire:model.live="saleProducts.{{ $index }}.product_id"
                        label="{{ __('Producto') }}">
                        <option value="">Seleccionar...</option>
                        @foreach ($products as $availableProduct)
                            <option value="{{ $availableProduct->id }}">
                                {{ $availableProduct->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <!-- Quantity -->
                <flux:field>
                    <x-money-input wire:model.live="saleProducts.{{ $index }}.quantity"
                        label="{{ __('Cantidad') }}"
                        class="text-[var(--color-text)]!" />
                </flux:field>

                <!-- Price per Unit -->
                <flux:field>
                    <x-money-input wire:model.live="saleProducts.{{ $index }}.price_per_unit"
                        label="{{ __('Precio Unitario') }}"
                        class="text-[var(--color-text)]!" />
                </flux:field>
            </div>

            @if (is_numeric($product['quantity']) &&
                    is_numeric($product['price_per_unit']) &&
                    $product['quantity'] > 0 &&
                    $product['price_per_unit'] > 0)
                <div class="mt-3 text-right">
                    <span class="text-sm font-medium text-gray-900">
                        Subtotal:
                        ${{ number_format((float) $product['quantity'] * (float) $product['price_per_unit'], 2) }}
                    </span>
                </div>
            @endif
        </div>
    @endforeach

    <!-- Total -->
    @php
        $total = collect($saleProducts)->sum(function ($product) {
            $quantity = (float) ($product['quantity'] ?? 0);
            $pricePerUnit = (float) ($product['price_per_unit'] ?? 0);
            return $quantity * $pricePerUnit;
        });
    @endphp
    @if ($total > 0)
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-blue-900">Total:</span>
                <span class="text-xl font-bold text-blue-900">${{ number_format($total, 2) }}</span>
            </div>
        </div>
    @endif
</div>
