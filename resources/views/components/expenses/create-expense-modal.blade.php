<flux:modal wire:model="showCreateExpenseModal" class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Crear Nuevo Gasto') }}</flux:heading>
    </div>

    <form wire:submit.prevent="createExpense" class="space-y-4">
         @if(!$contextUserId && in_array($currentUser->role, ['admin', 'coordinator']))
            <flux:field class="md:col-span-2">
                <flux:label>{{ __('Seleccionar Usuario') }}</flux:label>
                <flux:select wire:model="user_id" placeholder="{{ __('Buscar usuario...') }}">
                    @foreach($users as $user)
                        <flux:select.option value="{{ $user->id }}" >{{ $user->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="user_id" />
            </flux:field>
        @endif


        @if (!$contextRouteId)
        <flux:field>
            <flux:label>{{ __('Seleccionar Ruta') }}</flux:label>
            <flux:radio.group wire:model="route_id">
                @foreach($routes as $route)
                    <flux:radio value="{{ $route->id }}"
                        label="{{ $route->title }}"
                        description="{{ $route->created_at->format('d/m/Y') }} - {{ $route->carrier->name }}"
                    />
                @endforeach
            </flux:radio.group>
            <flux:error name="route_id" />
        </flux:field>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:input wire:model="description" label="{{ __('Descripción') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:input wire:model="amount" type="number" step="0.01" label="{{ __('Monto') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="amount" />
            </flux:field>
        </div>

        <flux:field>
            <flux:textarea wire:model="notes" label="{{ __('Notas') }}"
            placeholder="{{ __('Notas adicionales sobre el usuario') }}" rows="3"
            class="text-[var(--color-text)]!" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
            <flux:button variant="outline" wire:click="closeModals" class="w-full sm:w-auto">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Crear Gasto') }}</flux:button>
        </div>
    </form>
</flux:modal>