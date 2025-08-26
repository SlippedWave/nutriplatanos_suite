<div>
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
                                <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil"
                                            wire:click="openEditModal({{ $camera->id }})"
                                            aria-label="{{ __('Editar cámara') }}"
                                        />
                                <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash"
                                            wire:click="confirmDelete({{ $camera->id }})"
                                            aria-label="{{ __('Eliminar cámara') }}"
                                        />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay cámaras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

