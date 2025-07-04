<flux:modal wire:model="showCreateNoteModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Añadir Nueva Nota') }}</flux:heading>
    </div>
    <form wire:submit="createNote" class="space-y-4">
        <flux:field>
            <flux:textarea wire:model="content" label="{{ __('Notas:') }}" class="text-[var(--color-text)]!" />
            <flux:error name="content" />
        </flux:field>

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="ghost" wire:click="toggleCreateNoteModal">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Añadir Nota') }}</flux:button>
        </div>
    </form>
</flux:modal>