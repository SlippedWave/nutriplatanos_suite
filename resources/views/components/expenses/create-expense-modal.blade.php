<flux:modal wire:model="showCreateExpenseModal" class="space-y-4 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Nuevo Gasto') }}</flux:heading>
    </div>

    <form wire:submit="createExpense" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @if(!$contextUserId)
            <flux:field class="md:col-span-2">
                <flux:label>{{ __('Seleccionar Ruta') }}</flux:label>
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('Seleccionar') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Ruta') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Origen') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Destino') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routes as $route)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <flux:radio wire:model="selectedRouteId" value="{{ $route->id }}" />
                                </td>
                                <td class="px-3 py-2">{{ $route->name }}</td>
                                <td class="px-3 py-2">{{ $route->origin }}</td>
                                <td class="px-3 py-2">{{ $route->destination }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                    {{ __('No hay rutas disponibles') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <flux:error name="selectedRouteId" />
            </flux:field>

            @endif

            @if (!$contextRouteId)

            <flux:field>
                <flux:label>{{ __('Seleccionar Ruta') }}</flux:label>
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('Seleccionar') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Ruta') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Origen') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Destino') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routes as $route)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <flux:radio wire:model="selectedRouteId" value="{{ $route->id }}" />
                                </td>
                                <td class="px-3 py-2">{{ $route->name }}</td>
                                <td class="px-3 py-2">{{ $route->origin }}</td>
                                <td class="px-3 py-2">{{ $route->destination }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                    {{ __('No hay rutas disponibles') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <flux:error name="selectedRouteId" />
            </flux:field>

            @endif

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

        <flux:field>
            <flux:textarea wire:model="notes" label="{{ __('Notas') }}"
            placeholder="{{ __('Notas adicionales sobre el usuario') }}" rows="3"
            class="text-[var(--color-text)]!" />
            <flux:error name="notes" />
        </flux:field>

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
            <flux:button variant="outline" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Crear Usuario') }}</flux:button>
        </div>
    </form>
</flux:modal>