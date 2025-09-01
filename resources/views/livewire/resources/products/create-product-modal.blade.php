<flux:modal wire:model="showCreateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Crear nuevo producto') }}
        </flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="createProduct">
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold">{{ __('Datos del producto') }}</h4>
                <div class="flex flex-col space-y-2">
                    <flux:field>
                        <flux:input wire:model="name" label="{{ __('Nombre') }}" type="text"
                            placeholder="Nombre del producto" class="text-[var(--color-text)]!" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:textarea wire:model="description" label="{{ __('Descripción') }}" type="text"
                            placeholder="Describe el producto" class="text-[var(--color-text)]!" />
                        <flux:error name="description" />
                    </flux:field>
                </div>
                <div class="flex justify-end mt-4">
                    <flux:button wire:click="createProduct" variant="primary" wire:loading.attr="disabled"
                        wire:target="createProduct" class="w-full sm:w-auto">
                        <span wire:loading.remove wire:target="createProduct">{{ __('Crear') }}</span>
                        <span wire:loading wire:target="createProduct">Creando...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
