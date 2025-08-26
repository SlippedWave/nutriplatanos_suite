<flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Crear nueva cámara') }}</flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="createCamera">
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold">{{ __('Datos de la cámara') }}</h4>
                <div class="flex flex-col space-y-2">
                   <flux:field>
                        <flux:input 
                            wire:model="name" 
                            label="{{ __('Nombre') }}" 
                            type="text"
                            placeholder=""
                            class="text-[var(--color-text)]!"
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:input 
                            wire:model="location" 
                            label="{{ __('Ubicación') }}" 
                            type="text"
                            placeholder=""
                            class="text-[var(--color-text)]!"
                        />
                        <flux:error name="location" />
                    </flux:field>

                    <flux:field>
                        <flux:input 
                            wire:model="box_stock" 
                            label="{{ __('Stock de cajas') }}" 
                            type="text"
                            placeholder=""
                            class="text-[var(--color-text)]!"
                        />
                        <flux:error name="box_stock" />
                    </flux:field>
                </div>
            <div class="flex justify-end mt-4">
                <flux:button 
                    wire:click="createCamera" 
                    variant="primary" 
                    wire:loading.attr="disabled"
                    wire:target="createCamera"
                    class="w-full sm:w-auto"
                >
                    <span wire:loading.remove wire:target="createCamera">{{ __('Crear') }}</span>
                    <span wire:loading wire:target="createCamera">Creando...</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>

    