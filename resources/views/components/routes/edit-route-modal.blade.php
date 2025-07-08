<flux:modal wire:model="showEditRouteModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Editar ruta') }}</flux:heading>
    </div>
    <form wire:submit="updateRoute" class="space-y-4">
        <flux:field>
            <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!"/>
            <flux:error name="title" />
        </flux:field>

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="outline" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Guardar cambios') }}</flux:button>
        </div>
    </form>
</flux:modal>