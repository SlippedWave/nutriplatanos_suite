<?php
$role = auth()->user()->role ?? 'guest';
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen">
    <!-- Main Header -->
    <flux:header container class="border-b border-slate-50 shadow-lg ">
        <!-- Mobile menu toggle -->
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <!-- Desktop Navigation -->
        <flux:navbar class="hidden lg:flex ml-8">
            <flux:navbar.item 
                icon="layout-grid" 
                :href="route('dashboard')" 
                :current="request()->routeIs('dashboard')"
                wire:navigate
            >
                {{ __('Inicio') }}
            </flux:navbar.item>
            @if($role == 'admin' || $role == 'coordinator')
            <flux:navbar.item 
                icon="users" 
                :href="route('customers.index')" 
                :current="request()->routeIs('customers.*')"
                wire:navigate
            >
                {{ __('Clientes') }}
            </flux:navbar.item>
            @endif
            <flux:navbar.item 
                :current="request()->routeIs('routes.*')"
                icon="map" 
                :href="route('routes.index')"
                wire:navigate
            >
                {{ __('Rutas') }}
            </flux:navbar.item>
        </flux:navbar>
        <flux:spacer />
        <!-- User Menu -->
        <flux:dropdown position="top" align="end" class="hidden md:block">
            <flux:profile 
            class="cursor-pointer hover:bg-slate-100! " 
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
            :name="auth()->user()->name"
            avatar:name="auth()->user()->name"
            avatar:class="bg-banana-100! text-banana-900! font-medium! text-sm!"
            />

            <flux:menu class="w-55 bg-background! border! border-gray-200! shadow-lg! rounded-lg! space-y-2! p-2!">
            <!-- User Info -->
            <div class="">
                <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-banana-100 text-banana-900 font-medium text-sm">
                    {{ auth()->user()->initials() }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">
                    {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-[var(--color-gray-600)] truncate">
                    {{ auth()->user()->email }}
                    </p>
                </div>
                </div>
            </div>

            <!-- Menu Items -->
            <flux:menu.item 
                :href="route('settings.profile')" 
                icon="cog" 
                wire:navigate
                class="w-full text-left hover:bg-slate-100!"
            >
                {{ __('Configuración') }}
            </flux:menu.item>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item 
                as="button" 
                type="submit" 
                icon="arrow-right-start-on-rectangle" 
                class="w-full text-left text-warning-600! hover:bg-warning-50! hover:text-warning-700!"
                >
                {{ __('Cerrar sesión') }}
                </flux:menu.item>
            </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Sidebar -->
    <flux:sidebar 
        stashable 
        sticky 
        class="lg:hidden border-e border-gray-200! bg-background! shadow-lg! gap-2!"
    >
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <!-- Mobile Logo -->
        <div class="p-1 border-b border-slate-100">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>
        </div>

        <!-- Mobile Navigation -->
        <div class="p-1!">
            <flux:navlist variant="outline">
                <flux:navlist.item 
                    icon="layout-grid" 
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" 
                    wire:navigate
                >
                    {{ __('Inicio') }}
                </flux:navlist.item>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'coordinator')
                    <flux:navlist.item 
                        icon="users" 
                        :href="route('customers.index')" 
                        :current="request()->routeIs('customers.*')"
                        wire:navigate
                    >
                        {{ __('Clientes') }}
                    </flux:navlist.item>
                @endif
            </flux:navlist>
        </div>

        <flux:spacer />

        <!-- Mobile User Section -->
        <div class="p-2 border-t border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--color-primary-100)] text-[var(--color-primary-700)] font-medium">
                    {{ auth()->user()->initials() }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[var(--color-text)] truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-[var(--color-gray-600)] truncate">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </div>
            
            <flux:navlist variant="outline" class="space-y-1">
                <flux:navlist.item 
                    :href="route('settings.profile')" 
                    icon="cog" 
                    wire:navigate
                    class="w-full"
                >
                    {{ __('Configuración') }}
                </flux:navlist.item>
            </flux:navlist>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <flux:button 
                    type="submit" 
                    variant="ghost" 
                    icon="arrow-right-start-on-rectangle"
                    class="w-full justify-start text-warning-600! hover:bg-warning-50! hover:text-warning-700!"
                >
                    {{ __('Cerrar sesión') }}
                </flux:button>
            </form>
        </div>
    </flux:sidebar>

    <!-- Main Content Container with max-width matching navbar -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>

    @fluxScripts
</body>

</html>
