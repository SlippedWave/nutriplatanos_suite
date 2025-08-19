<div class="w-full">
    <div class="flex flex-col items-center w-full">
        <flux:button wire:click="$set('showCreateModal', true)" variant="primary" icon="plus" class="max-w-[220px]">
            {{ __('Crear nueva ruta') }}
        </flux:button>
    </div>

    <flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Crear Nueva Ruta') }}</flux:heading>
        </div>

        @if (session()->has('error'))
            <div class="bg-danger-50 border border-danger-200 text-danger-700 px-3 py-2 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="createRoute" class="space-y-4">
            <flux:field>
                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="title" />
            </flux:field>

            <livewire:routes.box-movements-editor
                wire:model="boxMovements"
                :cameras="$cameras"
                :editable="true"
            />

            <flux:field>
                <flux:textarea wire:model="notes" label="{{ __('Notas adicionales') }}" class="text-[var(--color-text)]!" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
                <flux:button variant="outline" type="button" wire:click="$set('showCreateModal', false)" class="w-full sm:w-auto">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" wire:click="createRoute" variant="primary" class="w-full sm:w-auto">
                    {{ __('Crear Ruta') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>