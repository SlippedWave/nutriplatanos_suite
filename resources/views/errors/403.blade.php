<x-layouts.errors title="Acceso no autorizado">
    <div class="flex flex-col items-center justify-center px-6 mt-4">
        <div class="w-full max-w-md text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-danger-100">
                    <svg class="h-10 w-10 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Error Content -->
            <div class="mb-8">
                <h1 class="text-4xl! font-bold text-[var(--color-text)] mb-2">403</h1>
                <h2 class="text-xl font-semibold text-[var(--color-text)] mb-4">Acceso no autorizado</h2>
                <p class="text-[var(--color-gray-600)] mb-6">
                    No tienes permiso para acceder a esta página. Por favor, contacta al administrador si crees que esto es un error.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <flux:button onclick="history.back()" variant="primary" class="w-full">
                    <flux:icon.arrow-left class="size-4" />
                    Volver atrás
                </flux:button>
                
                <flux:button href="{{ route('dashboard') }}" variant="outline" class="w-full" wire:navigate>
                    <flux:icon.home class="size-4" />
                    Ir al inicio
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts.errors>