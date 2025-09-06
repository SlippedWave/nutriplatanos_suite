<flux:modal wire:model="showViewModal" class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-sm md:max-w-md lg:max-w-xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Detalles de la Ruta') }}</flux:heading>
    </div> 

    @if($selectedRoute)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-lg">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-xl font-semibold">{{ $selectedRoute->title ?? 'Ruta del ' . $selectedRoute->created_at->format('d/m/Y') }}</h3>
                    <p class="text-gray-600">{{ $selectedRoute->carrier_name ?? 'Sin transportista asignado' }}</p>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-medium text-gray-900">{{ __('Información de la Ruta') }}</h4>
                <div class="mt-2 text-sm text-gray-600">
                    @if($selectedRoute->title)
                        <p><strong>{{ __('Título:') }}</strong> {{ $selectedRoute->title }}</p>
                    @endif
                    <p><strong>{{ __('Fecha:') }}</strong> {{ $selectedRoute->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>{{ __('Transportista:') }}</strong> {{ $selectedRoute->carrier_name ?? 'No asignado' }}</p>
                    <p>
                        <strong>{{ __('Estado:') }}</strong> 
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2 bg-{{$selectedRoute->getStatusColorAttribute()}}-100 text-{{$selectedRoute->getStatusColorAttribute()}}-800">
                            {{ $selectedRoute->status_label }}
                        </span>
                    </p>
                    @if($selectedRoute->closed_at)
                        <p><strong>{{ __('Cerrada:') }}</strong> {{ $selectedRoute->closed_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <div class="mt-3">
                    <a href="{{ route('routes.show', $selectedRoute->id) }}" class="text-primary-700 hover:text-primary-800 text-sm font-medium">
                        {{ __('Ver más detalles') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif
</flux:modal>
