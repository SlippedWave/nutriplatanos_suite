<div class="mb-6">
    <h1 class="text-2xl font-bold text-[var(--color-text)] mb-2">
        @if(request()->routeIs('dashboard'))
            Bienvenido, {{ $userName }}
        @else
            Hola, {{ $userName }}
        @endif
    </h1>
    <p class="text-[var(--color-gray-600)]">
        {{ $welcomeMessage }}
    </p>
</div>