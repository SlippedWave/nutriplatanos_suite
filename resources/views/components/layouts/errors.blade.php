<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => $title . ' - ' . config('app.name')])
</head>

<body class="min-h-screen bg-[var(--color-background)] antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
        <div class="w-full max-w-md text-center">
            <!-- Logo -->
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2" wire:navigate>
                    <x-app-logo-icon class="h-12 w-12" />
                    <span class="text-lg font-semibold text-[var(--color-text)]">{{ config('app.name') }}</span>
                </a>
            </div>

            {{ $slot }}
        </div>
    </div>
    @fluxScripts
</body>
</html>