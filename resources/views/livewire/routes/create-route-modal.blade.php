<div class="w-full">
    <div class="flex flex-col items-center w-full">
        <flux:button wire:click="$set('showCreateModal', true)" variant="primary" icon="plus" class="max-w-[220px]">
            {{ __('Crear nueva ruta') }}
        </flux:button>
    </div>

    <flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-2xl md:max-w-3xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Crear Nueva Ruta') }}</flux:heading>
        </div>

        @if (session()->has('error'))
            <div class="bg-danger-50 border border-danger-200 text-danger-700 px-3 py-2 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="createRoute" class="space-y-4">
            <flux:field>
                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
                <flux:error name="title" />
            </flux:field>

            @foreach($boxMovements as $index => $boxMovement)
                <div class="bg-gray-50 p-4 rounded-lg mb-4" wire:key="box-movement-{{ $index }}">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">{{ __('Movimiento de caja') }} {{ $index + 1 }}</span>
                        @if(count($boxMovements) >= 1)
                            <flux:button type="button" size="xs" variant="ghost" class="text-danger-600!" wire:click="removeBoxMovement({{ $index }})">
                                {{ __('Eliminar') }}
                            </flux:button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Camera -->
                        <flux:field>
                            <flux:label>{{ __('Seleccionar Cámara') }}</flux:label>
                            <flux:select wire:model="boxMovements.{{ $index }}.camera_id" placeholder="{{ __('Buscar cámara...') }}">
                                @foreach($cameras as $camera)
                                    <flux:select.option value="{{ $camera->id }}">{{ $camera->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="boxMovements.{{ $index }}.camera_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Tipo de movimiento') }}</flux:label>
                            <flux:select group wire:model="boxMovements.{{ $index }}.movement_type">
                                @foreach(App\Models\BoxMovement::MOVEMENT_TYPES as $type_key => $type)
                                    <flux:select.option value="{{ $type_key }}">{{ __(ucfirst($type)) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="boxMovements.{{ $index }}.movement_type" />
                        </flux:field>

                        <flux:field>
                            <flux:input
                                wire:model="boxMovements.{{ $index }}.quantity"
                                type="number"
                                min="1"
                                step="1"
                                label="{{ __('Cantidad de cajas') }}"
                                placeholder="1"
                                class="text-[var(--color-text)]!"
                            />
                            <flux:error name="boxMovements.{{ $index }}.quantity" />
                        </flux:field>

                        <flux:field class="md:col-span-3">
                            <flux:label>{{ __('Contenido de la(s) caja(s)') }}</flux:label>
                            <flux:select wire:model="boxMovements.{{ $index }}.box_content_status">
                                @foreach (App\Models\BoxMovement::BOX_CONTENT_STATUSES as $status_key => $status)
                                    <flux:select.option value="{{ $status_key }}">{{ __(ucfirst($status)) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="boxMovements.{{ $index }}.box_content_status" />
                        </flux:field>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-start">
                <flux:button type="button" variant="outline" icon="plus" wire:click="addBoxMovement">
                    {{ __('Agregar movimiento') }}
                </flux:button>
            </div>

            <flux:field>
                <flux:textarea wire:model="notes" label="{{ __('Notas adicionales') }}" class="text-[var(--color-text)]!" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
                <flux:button variant="outline" type="button" wire:click="$set('showCreateModal', false)" class="w-full sm:w-auto">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" wire:click="createRoute" variant="primary" class="w-full sm:w-auto">
                    {{ __('Crear Ruta') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>