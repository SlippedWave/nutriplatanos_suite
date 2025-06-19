<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen">
    <!-- Main Header -->
    <flux:header container class="border-b border-[var(--color-gray-200)] shadow-sm">
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
                {{ __('Panel de inicio') }}
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <!-- Search Button -->
        <flux:button 
            variant="ghost" 
            size="sm" 
            icon="magnifying-glass" 
            class="hidden md:inline-flex mr-2"
            aria-label="{{ __('Buscar') }}"
        />

        <!-- User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile 
                class="cursor-pointer" 
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down"
                :name="auth()->user()->name"
                avatar:name="auth()->user()->name"
                avatar:color="auto"
            />

            <flux:menu class="w-56">
                <!-- User Info -->
                <div class="">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[var(--color-primary-100)] text-[var(--color-primary-700)] font-medium text-sm">
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
                </div>

                <flux:menu.separator />

                <!-- Menu Items -->
                <flux:menu.item 
                    :href="route('settings.profile')" 
                    icon="cog" 
                    wire:navigate
                >
                    {{ __('Configuración') }}
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item 
                        as="button" 
                        type="submit" 
                        icon="arrow-right-start-on-rectangle" 
                        class="w-full text-left text-warning-600 hover:bg-warning-50 hover:text-warning-700"
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
        class="lg:hidden border-e border-[var(--color-gray-200)] bg-[var(--color-background)] "
    >
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <!-- Mobile Logo -->
        <div class="p-4 border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>
        </div>

        <!-- Mobile Navigation -->
        <div class="p-4">
            <flux:navlist variant="outline">
                <flux:navlist.item 
                    icon="layout-grid" 
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" 
                    wire:navigate
                >
                    {{ __('Panel de inicio') }}
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <flux:spacer />

        <!-- Mobile User Section -->
        <div class="p-4 border-t border-gray-100">
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
                    class="w-full justify-start text-red-600 hover:bg-red-50 hover:text-red-700"
                >
                    {{ __('Cerrar sesión') }}
                </flux:button>
            </form>
        </div>
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts
</body>

</html>
