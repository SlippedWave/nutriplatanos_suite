<x-layouts.errors title="Página no encontrada">
    <div class="flex flex-col items-center justify-center px-6 mt-4">
        <div class="w-full max-w-md text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-danger-100">
                    <svg class="h-10 w-10 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- Error Content -->
            <div class="mb-8">
                <h1 class="text-4xl! font-bold text-[var(--color-text)] mb-2">404</h1>
                <h2 class="text-xl font-semibold text-[var(--color-text)] mb-4">Página no encontrada</h2>
                <p class="text-[var(--color-gray-600)] mb-6">
                    Lo sentimos, la página que buscas no existe o ha sido movida.
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