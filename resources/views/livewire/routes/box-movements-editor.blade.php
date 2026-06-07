<div class="space-y-4">
    @foreach($model as $index => $movement)
        <div class="bg-gray-50 p-4 rounded-lg" wire:key="box-movement-{{ $index }}">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">{{ __('Movimiento de caja') }} {{ $index + 1 }}</span>
                @if($editable && count($model) >= 1)
                    <flux:button type="button" size="xs" variant="ghost" class="text-danger-600!" wire:click="removeMovement({{ $index }})">
                        {{ __('Eliminar') }}
                    </flux:button>
                @endif
            </div>

            @php
                $needsCamera = in_array($movement['movement_type'] ?? '', ['warehouse_to_route', 'route_to_warehouse']);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <flux:field>
                    <flux:label>{{ __('Tipo de movimiento') }}</flux:label>
                    <flux:select group wire:model="model.{{ $index }}.movement_type" :disabled="!$editable">
                        @foreach(App\Models\BoxMovement::MOVEMENT_TYPES as $type_key => $type)
                            <flux:select.option value="{{ $type_key }}">{{ __(ucfirst($type)) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                @if($needsCamera)
                <flux:field>
                    <flux:label>{{ __('Cámara') }}</flux:label>
                    <flux:select wire:model="model.{{ $index }}.camera_id" :disabled="!$editable">
                        @foreach($cameras as $camera)
                            <flux:select.option value="{{ $camera->id }}">
                                {{ $camera->name }} ({{ $camera->getCurrentStock() }} cajas)
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                @endif

                <flux:field>
                    <flux:input
                        wire:model="model.{{ $index }}.quantity"
                        type="number"
                        min="1"
                        step="1"
                        label="{{ __('Cantidad de cajas') }}"
                        placeholder="1"
                        class="text-[var(--color-text)]!"
                        :disabled="!$editable"
                    />
                </flux:field>

                <flux:field class="md:col-span-3">
                    <flux:label>{{ __('Contenido de la(s) caja(s)') }}</flux:label>
                    <flux:select wire:model="model.{{ $index }}.box_content_status" :disabled="!$editable">
                        @foreach (App\Models\BoxMovement::BOX_CONTENT_STATUSES as $status_key => $status)
                            <flux:select.option value="{{ $status_key }}">{{ __(ucfirst($status)) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        </div>
    @endforeach

    @if($editable)
        <div class="flex justify-start">
            <flux:button type="button" variant="outline" icon="plus" wire:click="addMovement">
                {{ __('Agregar movimiento') }}
            </flux:button>
        </div>
    @endif
</div>