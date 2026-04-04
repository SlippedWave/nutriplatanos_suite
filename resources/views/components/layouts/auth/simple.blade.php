<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => config('app.name') . ' - Iniciar Sesión'])
</head>

<body class="min-h-screen bg-[var(--color-background)] antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium mb-6" wire:navigate>
                <x-app-logo-icon/>
                <span class="text-lg font-semibold text-[var(--color-text)]">{{ config('app.name') }}</span>
                <span class="text-sm text-[var(--color-gray-600)]">Suite de Gestión de Recursos Empresariales</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
