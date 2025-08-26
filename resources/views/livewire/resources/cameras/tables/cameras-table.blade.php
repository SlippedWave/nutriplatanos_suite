<div>
     @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'cameras-table')
        @php
            $type = data_get($flash, 'type', 'info');
        @endphp

        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition
            @class([
                'px-4 py-3 rounded-lg flex justify-between items-center',
                'bg-green-50 border border-green-200 text-green-700' => $type === 'success',
                'bg-danger-50 border border-danger-200 text-danger-700' => $type === 'error',
                'bg-yellow-50 border border-yellow-200 text-yellow-700' => $type === 'warning',
                'bg-blue-50 border border-blue-200 text-blue-700' => !in_array($type, ['success','error','warning']),
            ])
        >
            <div>{{ data_get($flash, 'text') }}</div>
            <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <div class="flex flex-col gap-4 mb-4">
        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <!-- Filters Group -->
            <div class="flex flex-col xs:flex-row gap-2 flex-1 max-w-sm">
                <!-- Per Page -->
                <flux:select wire:model.live="perPage" class="xs:w-16 flex-none">
                    <option value="3">3</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                </flux:select>
            </div>
            
            <!-- Action Buttons Group -->
            <div class="flex flex-col xs:flex-row gap-2 xs:items-center">
                <flux:button variant="primary"
                                icon="plus" 
                                wire:click="$dispatch('open-create-camera-modal')"
                                class="w-full xs:w-auto">
                    <span class="hidden sm:inline">{{ __('Nueva Venta') }}</span>
                    <span class="sm:hidden">{{ __('Nueva') }}</span>
                </flux:button>
            </div>
        </div>
    </div>


    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('camera_id')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center justify-center">
                                <span>Cámara</span>
                                @if($sortField === 'camera_id')
                                    <flux:icon.chevron-up class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            <div class="flex items-center justify-center">
                                <span>Cantidad de cajas</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            <div class="flex items-center justify-center">
                                <span>Acciones</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($cameras as $camera)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $camera->name }}
                                <div class="text-xs text-gray-500">
                                    {{ $camera->location }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $camera->box_stock }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil"
                                            wire:click="$dispatch('open-update-camera-modal', {id: {{ $camera->id }}})"
                                            aria-label="{{ __('Editar cámara') }}"
                                        />
                                <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash"
                                            wire:click="$dispatch('open-delete-camera-modal', {id: {{ $camera->id }}})"
                                            aria-label="{{ __('Eliminar cámara') }}"
                                        />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay cámaras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:resources.cameras.create-camera-modal />
    <livewire:resources.cameras.update-camera-modal />
    <livewire:resources.cameras.delete-camera-modal />
</div>

