<flux:modal wire:model="showUpdateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Actualizar cámara') }}</flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="updateCamera">
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold">{{ __('Datos de la cámara') }}</h4>
                <div class="flex flex-col space-y-2">
                    <flux:field>
                        <flux:input wire:model="name" label="{{ __('Nombre') }}" type="text"
                            placeholder="Nombre de la cámara" class="text-[var(--color-text)]!" />
                    </flux:field>

                    <flux:field>
                        <flux:input wire:model="location" label="{{ __('Ubicación') }}" type="text"
                            placeholder="Ubicación de la cámara" class="text-[var(--color-text)]!" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Stock actual') }}</flux:label>
                        <flux:description>{{ __('Calculado: cajas base + movimientos registrados.') }}</flux:description>
                        <p class="text-sm font-semibold py-1">{{ $current_stock }}</p>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Cajas base') }}</flux:label>
                        <flux:description>{{ __('Cajas físicas almacenadas en la cámara, sin contar movimientos de rutas.') }}</flux:description>
                        <flux:input wire:model="box_stock" type="number" min="0"
                            placeholder="0" class="text-[var(--color-text)]!" />
                    </flux:field>
                </div>
                <div class="flex justify-end mt-4">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                        wire:target="updateCamera" class="w-full sm:w-auto">
                        <span wire:loading.remove wire:target="updateCamera">{{ __('Actualizar') }}</span>
                        <span wire:loading wire:target="updateCamera">Actualizando...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
