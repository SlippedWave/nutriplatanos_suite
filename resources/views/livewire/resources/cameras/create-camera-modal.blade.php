<flux:modal wire:model="showCreateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Crear nueva cámara') }}</flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="createCamera">
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
                        <flux:label>{{ __('Stock inicial de cajas') }}</flux:label>
                        <flux:description>{{ __('Cantidad de cajas actualmente en esta cámara antes de cualquier movimiento registrado.') }}</flux:description>
                        <flux:input wire:model="box_stock" type="number" min="0"
                            placeholder="0" class="text-[var(--color-text)]!" />
                    </flux:field>
                </div>
                <div class="flex justify-end mt-4">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                        wire:target="createCamera" class="w-full sm:w-auto">
                        <span wire:loading.remove wire:target="createCamera">{{ __('Crear') }}</span>
                        <span wire:loading wire:target="createCamera">Creando...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
