@props(['heading' => '', 'subheading' => ''])

@php
    $user = auth()->user();
    $role = $user->role ?? 'guest';
    $activeRoute = $user->routes()->where('status', 'active')->first();
    $userHasActiveRoute = $activeRoute !== null;
    $activeRouteId = $activeRoute?->id;
@endphp

<div class="flex-1">

    @if (request()->routeIs('settings.*'))
        <div class="flex items-start max-md:flex-col max-w-full">
            <div class="w-full md:w-[220px] mb-2! py-0! md:py-2! md:mb-3 md:flex-shrink-0">
                <flux:navlist>
                    <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Perfil') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Contraseña') }}</flux:navlist.item>
                    @if(auth()->user()->isAdmin())
                        <flux:navlist.item :href="route('settings.users')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                    @endif
                </flux:navlist>
            </div>

            <flux:separator class="md:hidden" />

            <div class="flex-1 self-stretch max-md:pt-6 min-w-0 max-w-full">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $heading ?? '' }}</flux:heading>
                        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
                    </div>

                    <div class="ml-4 flex-shrink-0">
                    </div>
                </div>

                <div class="mt-5 w-full max-w-full">
                    {{ $slot }}
                </div>

            </div>
        </div>
    @else
    <div class="w-full flex justify-center items-center mb-4! mt-0! pt-0! ">
        <flux:navbar>
            <flux:navbar.item :href="route('routes.index')" wire:navigate
                :current="request()->routeIs('routes.index') || request()->routeIs('routes.show')">
                {{ __('Ruta') }}</flux:navbar.item>
            <flux:navbar.item :href="route('routes.history')" wire:navigate
                :current="request()->routeIs('routes.history')">
                {{ $role === 'admin' || $role === 'coordinator' ? __('Ver historial de rutas') : __('Mis rutas') }}
            </flux:navbar.item>
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
    @endif
</div>
