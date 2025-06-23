<!-- Edit User Modal -->
<flux:modal wire:model="showEditModal" class="space-y-6 border-0! bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Editar Usuario') }}</flux:heading>
    </div>

    <form wire:submit="updateUser" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model="name" label="{{ __('Nombre completo') }}" requidanger class="text-slate-900" />
            <flux:input wire:model="email" type="email" label="{{ __('Correo electrónico') }}" requidanger class="text-slate-900" />
            <flux:input wire:model="phone" label="{{ __('Teléfono') }}" class="text-slate-900" />
            <flux:select wire:model="role" label="{{ __('Rol') }}" requidanger class="text-slate-900">
                <option value="carrier">{{ __('Transportista') }}</option>
                <option value="coordinator">{{ __('Coordinador') }}</option>
                <option value="admin">{{ __('Administrador') }}</option>
            </flux:select>
            <flux:input wire:model="curp" label="{{ __('CURP') }}" class="text-slate-900" />
            <flux:input wire:model="rfc" label="{{ __('RFC') }}" class="text-slate-900" />
        </div>

        <flux:textarea wire:model="address" label="{{ __('Dirección') }}" rows="2" class="text-slate-900" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:input wire:model="emergency_contact" label="{{ __('Contacto de emergencia') }}" class="text-slate-900" />
            <flux:input wire:model="emergency_contact_phone" label="{{ __('Teléfono de emergencia') }}" class="text-slate-900" />
            <flux:input wire:model="emergency_contact_relationship" label="{{ __('Relación') }}" class="text-slate-900" />
        </div>

        <div class="border-t pt-4">
            <flux:subheading>{{ __('Cambiar contraseña (opcional)') }}</flux:subheading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <flux:input wire:model="password" type="password" label="{{ __('Nueva contraseña') }}" class="text-slate-900" />
                <flux:input wire:model="password_confirmation" type="password" label="{{ __('Confirmar nueva contraseña') }}" class="text-slate-900" />
            </div>
        </div>

        <flux:checkbox wire:model="is_active" label="{{ __('Usuario activo') }}" />

        <div class="flex justify-end gap-3 pt-4">
            <flux:button variant="ghost" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Actualizar Usuario') }}</flux:button>
        </div>
    </form>
</flux:modal>