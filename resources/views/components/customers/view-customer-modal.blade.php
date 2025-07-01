<flux:modal wire:model="showViewModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Detalles del Cliente') }}</flux:heading>
    </div> 

    @if($selectedCustomer)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-lg">
                    {{ strtoupper(substr($selectedCustomer->name, 0, 1)) }}
                </span>
                <div>
                    <h3 class="text-xl font-semibold">{{ $selectedCustomer->name }}</h3>
                    <p class="text-gray-600">{{ $selectedCustomer->email }}</p>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-medium text-gray-900">{{ __('Información Adicional') }}</h4>
                <div class="mt-2 text-sm text-gray-600">
                    <p><strong>{{ __('Teléfono:') }}</strong> {{ $selectedCustomer->phone }}</p>
                    <p><strong>{{ __('Dirección:') }}</strong> {{ $selectedCustomer->address }}</p>
                    <p><strong>{{ __('RFC:') }}</strong> {{ $selectedCustomer->rfc }}</p>
                    <p><strong>{{ __('Estado:') }}</strong> {{ $selectedCustomer->active ? __('Activo') : __('Inactivo') }}</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('customers.show', $selectedCustomer->id) }}" class="text-primary-700 hover:text-primary-800 text-sm font-medium">
                        {{ __('Ver más detalles') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif
</flux:modal>