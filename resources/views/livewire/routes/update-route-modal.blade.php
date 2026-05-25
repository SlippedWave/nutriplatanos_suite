<div class="w-full">
    <flux:modal wire:model="showUpdateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Actualizar Ruta') }}</flux:heading>
        </div>

        <form wire:submit.prevent="updateRoute" class="space-y-4">
            <flux:field>
                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
            </flux:field>

            @if ($user && ($user->isAdmin() || $user->isCoordinator()))
                <flux:field>
                    <flux:label>{{ __('Asignar transportista') }}</flux:label>
                    <flux:select wire:model="carrier_id" placeholder="{{ __('Buscar transportista...') }}">
                        <flux:select.option value="">{{ __('Sin asignar') }}</flux:select.option>
                        @foreach($carriers as $carrier)
                            <flux:select.option value="{{ $carrier->id }}" >{{ $carrier->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @endif

            <livewire:routes.box-movements-editor
                wire:model="boxMovements"
                :cameras="$cameras"
                :editable="true"
            />

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
                <flux:button variant="outline" type="button" wire:click="$set('showUpdateModal', false)" class="w-full sm:w-auto">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" wire:click="updateRoute" variant="primary" class="w-full sm:w-auto">
                    {{ __('Actualizar Ruta') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>