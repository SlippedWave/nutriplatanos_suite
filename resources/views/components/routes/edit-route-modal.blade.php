<flux:modal wire:model="showEditRouteModal" class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-sm md:max-w-md lg:max-w-xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Editar ruta') }}</flux:heading>
    </div>
    <form wire:submit="updateRoute" class="space-y-4">
        <flux:field>
            <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!"/>
            <flux:error name="title" />
        </flux:field>

        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
            <flux:button variant="outline" wire:click="closeModals" class="w-full sm:w-auto">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Guardar cambios') }}</flux:button>
        </div>
    </form>
</flux:modal>