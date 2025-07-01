<flux:modal wire:model="showEditModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Editar Usuario') }}</flux:heading>
    </div>

    <form wire:submit="updateUser" class="space-y-4">
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
                <flux:select wire:model="role" label="{{ __('Rol') }}" required class="text-[var(--color-text)]!">
                    <option value="carrier">{{ __('Transportista') }}</option>
                    <option value="coordinator">{{ __('Coordinador') }}</option>
                    <option value="admin">{{ __('Administrador') }}</option>
                </flux:select>
                <flux:error name="role" />
            </flux:field>
            <flux:field>
                <flux:input wire:model="curp" label="{{ __('CURP') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="curp" />
            </flux:field>
            <flux:field>
                <flux:input wire:model="rfc" label="{{ __('RFC') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="rfc" />
            </flux:field>
        </div>

        <flux:textarea wire:model="address" label="{{ __('Dirección') }}" rows="2" class="text-[var(--color-text)]!" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:input wire:model="emergency_contact" label="{{ __('Contacto de emergencia') }}" class="text-[var(--color-text)]!" />
            <flux:input wire:model="emergency_contact_phone" label="{{ __('Teléfono de emergencia') }}" class="text-[var(--color-text)]!" />
            <flux:input wire:model="emergency_contact_relationship" label="{{ __('Relación') }}" class="text-[var(--color-text)]!" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model="password" type="password" label="{{ __('Contraseña') }}" required class="text-[var(--color-text)]!" />
            <flux:input wire:model="password_confirmation" type="password" label="{{ __('Confirmar contraseña') }}" required class="text-[var(--color-text)]!" />
        </div>

        <flux:checkbox wire:model="active" label="{{ __('Usuario activo') }}" />

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="ghost" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Guardar cambios') }}</flux:button>
        </div>
    </form>
</flux:modal>