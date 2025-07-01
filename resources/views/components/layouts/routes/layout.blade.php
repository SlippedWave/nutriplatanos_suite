

<div class="flex-1">
    <div class="w-full flex justify-center items-center mb-4! mt-0! pt-0! ">
        <flux:navbar>
            <flux:navbar.item :href="route('routes.index')" wire:navigate>{{ __('Mi ruta') }}</flux:navbar.item>
            <flux:navbar.item :href="route('routes.history')" wire:navigate>{{ ($role === 'admin' || $role === 'coordinator') ? __('Ver historial de rutas') : __('Mis rutas') }}</flux:navbar.item>
        </flux:navbar>
    </div>

    <flux:separator class="my-6" />

    <div class="flex-1 self-stretch min-w-0 max-w-full">
        <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <flux:heading>{{ $heading ?? '' }}</flux:heading>
                <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            </div>
        </div>

        <div class="mt-5 w-full max-w-full">
            {{ $slot }}
        </div>
    </div>
</div>