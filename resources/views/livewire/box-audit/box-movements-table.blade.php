<div class="space-y-4">
    {{-- Filters --}}
    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 sm:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <flux:field>
                <flux:label>{{ __('Cámara') }}</flux:label>
                <flux:select wire:model.live="cameraFilter">
                    <flux:select.option value="">{{ __('Todas') }}</flux:select.option>
                    @foreach($cameras as $camera)
                        <flux:select.option value="{{ $camera->id }}">{{ $camera->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Tipo') }}</flux:label>
                <flux:select wire:model.live="typeFilter">
                    <flux:select.option value="">{{ __('Todos') }}</flux:select.option>
                    @foreach(\App\Models\BoxMovement::MOVEMENT_TYPES as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:input wire:model.live.debounce.400ms="startDate" label="{{ __('Desde') }}" type="date" class="text-[var(--color-text)]!" />
            </flux:field>

            <flux:field>
                <flux:input wire:model.live.debounce.400ms="endDate" label="{{ __('Hasta') }}" type="date" class="text-[var(--color-text)]!" />
            </flux:field>
        </div>

        <div class="flex items-center justify-between mt-3">
            <flux:button
                wire:click="toggleSuperseded"
                variant="ghost"
                size="sm"
                class="{{ $includeSuperseded ? 'text-orange-600!' : 'text-gray-500!' }}"
            >
                {{ $includeSuperseded ? __('Ocultar reemplazados') : __('Incluir reemplazados') }}
            </flux:button>

            <flux:select wire:model.live="perPage" class="w-20">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </flux:select>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cámara</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruta</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transportista</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cajas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contenido</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                        @php
                            $superseded = $movement->trashed();
                            $typeColors = [
                                'warehouse_to_route'  => 'blue',
                                'route_to_warehouse'  => 'purple',
                                'route_to_route'      => 'yellow',
                                'truck_inventory'     => 'gray',
                            ];
                            $color = $typeColors[$movement->movement_type] ?? 'gray';
                            $typeLabel = \App\Models\BoxMovement::MOVEMENT_TYPES[$movement->movement_type] ?? $movement->movement_type;
                            $contentLabel = \App\Models\BoxMovement::BOX_CONTENT_STATUSES[$movement->box_content_status] ?? '—';
                        @endphp
                        <tr class="{{ $superseded ? 'opacity-40 bg-gray-50' : 'hover:bg-gray-50' }}">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $movement->moved_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                {{ $movement->camera?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                @if($movement->route)
                                    <a href="{{ route('routes.show', $movement->route_id) }}" class="text-primary-600 hover:underline" wire:navigate>
                                        {{ $movement->route->title }}
                                    </a>
                                @else
                                    —
                                @endif
                                @if($movement->movement_type === 'route_to_route' && $movement->relatedRoute)
                                    <span class="text-gray-400">{{ ($movement->transfer_direction === 'in') ? '←' : '→' }}</span>
                                    <a href="{{ route('routes.show', $movement->related_route_id) }}" class="text-primary-600 hover:underline" wire:navigate>
                                        {{ $movement->relatedRoute->title }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $movement->route?->carrier?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center font-semibold whitespace-nowrap">
                                {{ $movement->quantity }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $contentLabel }}
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                @if($superseded)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Invalidado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Confirmado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-400">
                                No hay movimientos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-2">
        {{ $movements->links() }}
    </div>
</div>
