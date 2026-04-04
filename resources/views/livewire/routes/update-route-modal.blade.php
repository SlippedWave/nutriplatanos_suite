<div class="w-full">
    <flux:modal wire:model="showUpdateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Actualizar Ruta') }}</flux:heading>
        </div>

        @if (session()->has('error'))
            <div class="bg-danger-50 border border-danger-200 text-danger-700 px-3 py-2 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="updateRoute" class="space-y-4">
            <flux:field>
                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="title" />
            </flux:field>

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