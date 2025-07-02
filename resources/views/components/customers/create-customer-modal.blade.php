<flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Cliente') }}</flux:heading>
    </div>

    <form wire:submit.prevent="createCustomer" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:input wire:model="name" label="{{ __('Nombre completo') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="name" />
            </flux:field>
            <flux:field>
                <flux:input wire:model="email" type="email" label="{{ __('Correo electrónico') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:input wire:model="phone" label="{{ __('Teléfono') }}" class="text-[var(--color-text)]!" />
                <flux:error name="phone" />
            </flux:field>
             <flux:field>
                <flux:input wire:model="rfc" label="{{ __('RFC') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="rfc" />
            </flux:field>
        </div>
        <flux:textarea wire:model="address" label="{{ __('Dirección') }}" rows="2" class="text-[var(--color-text)]!" />

        <flux:field>
            <flux:textarea wire:model="notes" label="{{ __('Notas adicionales') }}" class="text-[var(--color-text)]!" />
            <flux:error name="notes" />
        </flux:field>
        
        <flux:checkbox wire:model="active" label="{{ __('Cliente activo') }}" />

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="ghost" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Crear Cliente') }}</flux:button>
        </div>
    </form>
</flux:modal>


