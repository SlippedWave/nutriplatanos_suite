<x-layouts.app.header :title="$title ?? 'Nutriplatanos'">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.header>
