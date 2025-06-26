<flux:modal wire:model="showViewModal" class="space-y-6 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Detalles del Cliente') }}</flux:heading>
    </div> 

    @if($customer)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-lg">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </span>
                <div>
                    <h3 class="text-xl font-semibold">{{ $customer->name }}</h3>
                    <p class="text-gray-600">{{ $customer->email }}</p>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-medium text-gray-900">{{ __('Información Adicional') }}</h4>
                <div class="mt-2 text-sm text-gray-600">
                    <p><strong>{{ __('Teléfono:') }}</strong> {{ $customer->phone }}</p>
                    <p><strong>{{ __('Dirección:') }}</strong> {{ $customer->address }}</p>
                    <p><strong>{{ __('RFC:') }}</strong> {{ $customer->rfc }}</p>
                    <p><strong>{{ __('Estado:') }}</strong> {{ $customer->is_active ? __('Activo') : __('Inactivo') }}</p>
                </div>
            </div>
        </div>
    @endif
</flux:modal>