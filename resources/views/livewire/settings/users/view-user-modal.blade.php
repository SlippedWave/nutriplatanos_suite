<flux:modal wire:model="showViewModal" class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-sm md:max-w-md lg:max-w-xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Detalles del Usuario') }}</flux:heading>
    </div>

    @if($selectedUser)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-lg">
                    {{ $selectedUser->initials() }}
                </span>
                <div>
                    <h3 class="text-xl font-semibold">{{ $selectedUser->name }}</h3>
                    <p class="text-gray-600">{{ $selectedUser->email }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($selectedUser->role === 'admin') bg-yellow-100 text-yellow-800
                        @elseif($selectedUser->role === 'coordinator') bg-secondary-100 text-secondary-800
                        @else bg-blue-100 text-blue-800 @endif">
                        @if($selectedUser->role === 'admin') {{ __('Administrador') }}
                        @elseif($selectedUser->role === 'coordinator') {{ __('Coordinador') }}
                        @else {{ __('Transportista') }} @endif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h4 class="font-medium text-gray-900">{{ __('Información Personal') }}</h4>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">{{ __('Teléfono:') }}</span> {{ $selectedUser->phone ?: '-' }}</p>
                        <p><span class="font-medium">{{ __('CURP:') }}</span> {{ $selectedUser->curp ?: '-' }}</p>
                        <p><span class="font-medium">{{ __('RFC:') }}</span> {{ $selectedUser->rfc ?: '-' }}</p>
                        <p><span class="font-medium">{{ __('Dirección:') }}</span> {{ $selectedUser->address ?: '-' }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-medium text-gray-900">{{ __('Contacto de Emergencia') }}</h4>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">{{ __('Nombre:') }}</span> {{ $selectedUser->emergency_contact ?: '-' }}</p>
                        <p><span class="font-medium">{{ __('Teléfono:') }}</span> {{ $selectedUser->emergency_contact_phone ?: '-' }}</p>
                        <p><span class="font-medium">{{ __('Relación:') }}</span> {{ $selectedUser->emergency_contact_relationship ?: '-' }}</p>
                    </div>
                </div>
            </div>

            @livewire('notes.notes-displayer', [
                'notable_type' => \App\Models\User::class, 
                'notable_id' => $selectedUser->id
            ], key('notes-' . $selectedUser->id))
        

            <div>
                <h4 class="font-medium text-gray-900 mb-3">{{ __('Información del Sistema') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <p><span class="font-medium">{{ __('Fecha de registro:') }}</span> {{ $selectedUser->created_at->format('d/m/Y H:i') }}</p>
                    <p><span class="font-medium">{{ __('Último acceso:') }}</span> {{ $selectedUser->last_login_at?->format('d/m/Y H:i') ?: __('Nunca') }}</p>
                    <p><span class="font-medium">{{ __('Estado:') }}</span> 
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $selectedUser->active ? 'bg-green-100 text-green-800' : 'bg-danger-100 text-danger-800' }}">
                            {{ $selectedUser->active ? __('Activo') : __('Inactivo') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex justify-end pt-4 flex-col sm:flex-row">
        <flux:button variant="primary" wire:click="$set('showViewModal', false)" class="w-full sm:w-auto">{{ __('Cerrar') }}</flux:button>
    </div>
</flux:modal>