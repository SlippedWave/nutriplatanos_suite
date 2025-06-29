<flux:modal wire:model="showDeleteModal" class="space-y-4 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="text-danger-800!">{{ __('Eliminar Ruta') }}</flux:heading>
    </div>

    @if($selectedRoute)
        <div class="space-y-4">
            <div class="bg-danger-50 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-danger-800!" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-danger-800!">{{ __('Advertencia') }}</h3>
                        <div class="mt-2 text-sm text-danger-700!">
                            <p>{{ __('Esta acción no se puede deshacer. Se eliminará permanentemente la ruta y todos sus datos asociados (ventas, gastos, movimientos de caja).') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">{{ __('Ruta a eliminar:') }}</h4>
                <div class="mt-2 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </span>
                    <div>
                        <p class="font-medium">Ruta del {{ $selectedRoute->date->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-600">{{ $selectedRoute->carrier_name ?? 'Sin transportista asignado' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex justify-end gap-3 pt-4">
        <flux:button variant="ghost" wire:click="closeModals">{{ __('Cancelar') }}</flux:button>
        <flux:button variant="danger" class="text-background!" wire:click="deleteRoute">{{ __('Eliminar Ruta') }}</flux:button>
    </div>
</flux:modal>
