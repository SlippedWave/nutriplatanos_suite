<flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Nueva Ruta') }}</flux:heading>
    </div>
            <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
    <form wire:submit="createRoute" class="space-y-4">
        <flux:field>
            <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" value="{{"Ruta del día" . now()}}"/>
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:textarea wire:model="notes" label="{{ __('Notas adicionales') }}" class="text-[var(--color-text)]!" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="ghost" wire:click="$set('showCreateModal', false)">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Crear Ruta') }}</flux:button>
        </div>
    </form>
</flux:modal>