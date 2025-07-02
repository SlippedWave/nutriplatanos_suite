<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 4000)" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex justify-between items-center">
            <div>{{ session('message') }}</div>
            <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 4000)" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg flex justify-between items-center">
            <div>{{ session('error') }}</div>
            <button type="button" @click="show = false" class="text-danger-500 hover:text-danger-700">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="flex items-center gap-4">
        <div class="flex-1">
            <flux:input 
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Buscar usuarios...') }}"
            />
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="perPage" class="w-20">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </flux:select>
            <flux:button 
                variant="primary" 
                icon="plus"
                wire:click="openCreateModal"
            >
                {{ __('Nuevo Usuario') }}
            </flux:button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Table Header -->
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 w-[300px]" 
                            wire:click="sortBy('name')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Nombre') }}
                                @if($sortField === 'name')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 max-w-[220px]" 
                            wire:click="sortBy('email')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Correo electrónico') }}
                                @if($sortField === 'email')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 max-w-[220px]" 
                            wire:click="sortBy('phone')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Teléfono') }}
                                @if($sortField === 'phone')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>
                        

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 max-w-[220px]" 
                            wire:click="sortBy('role')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Rol') }}
                                @if($sortField === 'role')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 max-w-[220px]" 
                            wire:click="sortBy('created_at')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Fecha de registro') }}
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 max-w-[220px]" 
                            wire:click="sortBy('last_login_at')">
                            <div class="flex items-center gap-2 justify-center">
                                {{ __('Último acceso') }}
                                @if($sortField === 'last_login_at')
                                    @if($sortDirection === 'asc')
                                        <flux:icon.chevron-up class="w-4 h-4" />
                                    @else
                                        <flux:icon.chevron-down class="w-4 h-4" />
                                    @endif
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider max-w-[220px]">
                            {{ __('Dirección') }}
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider max-w-[220px]">
                            {{ __('Contacto de emergencia') }}
                        </th>
                        
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider max-w-[220px]">
                            {{ __('Acciones') }}
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors ">
                            <!-- Name Column -->
                            <td class="px-6 py-4 w-[300px] text-center">
                                <div class="flex items-center gap-3 justify-center">
                                    <div>
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-sm">
                                            {{ $user->initials() }}
                                        </span>
                                        
                                    </div>
                                    
                                    <div class="min-w-0 flex-col items-center justify-center text-center">
                                        <div class="text-sm font-medium text-gray-900 break-words">{{ $user->name }}</div>
                                        @switch($user->role)
                                            @case('admin')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-primary-100 text-yellow-800">
                                                    {{ __('Admin') }}
                                                </span>
                                                @break
                                                @case('carrier')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-slate-100 text-blue-800">
                                                    {{ __('Transportista') }}
                                                </span>
                                                @break
                                                @case('coordinator')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-secondary-100 text-green-800">
                                                    {{ __('Coordinador') }}
                                                </span>
                                                @break
                                        @endswitch

                                        @if(isset($user->curp) && $user->curp)
                                            <div class="text-sm text-gray-500 break-words">CURP: {{ $user->curp }}</div>
                                        @endif
                                        @if(isset($user->rfc) && $user->rfc)
                                            <div class="text-sm text-gray-500 break-words">RFC: {{ $user->rfc }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Email Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="min-w-0">
                                    <div class="text-sm text-gray-900 break-words">{{ $user->email }}</div>
                                </div>
                            </td>

                            <!-- Phone Column -->
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-[220px] break-words text-center">
                                {{ $user->phone ?? '-' }}
                            </td>

                            <!-- Role Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="text-sm text-gray-900">
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full  
                                                text-xs font-medium bg-primary-100 text-yellow-800">
                                                {{ __('Admin') }}
                                            </span>
                                            @break
                                        @case('carrier')
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full  
                                                text-xs font-medium bg-slate-100 text-blue-800">
                                                {{ __('Transportista') }}
                                            </span>
                                            @break
                                        @case('coordinator')
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full  
                                                text-xs font-medium bg-secondary-100 text-green-800">
                                                {{ __('Coordinador') }}
                                            </span>
                                            @break
                                    @endswitch
                                </div>
                            </td>
                            
                            <!-- Created At Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="text-sm text-gray-900">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $user->created_at->format('H:i') }}
                                </div>
                            </td>

                            <!-- Last Login Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                @if($user->last_login_at)
                                    <div class="text-sm text-gray-900">
                                        {{ $user->last_login_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $user->last_login_at->format('H:i') }}
                                    </div>
                                    @if($user->last_login_ip)
                                        <div class="text-xs text-gray-400">
                                            IP: {{ $user->last_login_ip }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500">
                                        {{ __('Nunca') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Address Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="text-sm text-gray-900 break-words">{{ $user->address ?? '-' }}</div>
                            </td>

                            <!-- Emergency Contact Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="min-w-0">
                                    <div class="text-sm text-gray-900 break-words">{{ $user->emergency_contact }}</div>
                                    @if($user->emergency_contact_relationship)
                                        <div class="text-sm text-gray-500 break-words">{{ $user->emergency_contact_relationship }}</div>
                                    @endif
                                    @if($user->emergency_contact_phone)
                                        <div class="text-sm text-gray-500 break-words">{{ $user->emergency_contact_phone }}</div>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Actions Column -->
                            <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="flex items-center gap-2 justify-center">
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm" 
                                        icon="pencil"
                                        wire:click="openEditModal({{ $user->id }})"
                                        aria-label="{{ __('Editar usuario') }}"
                                    />
                                    
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm" 
                                        icon="eye"
                                        wire:click="openViewModal({{ $user->id }})"
                                        aria-label="{{ __('Ver usuario') }}"
                                    />
                                    
                                    @if($user->id !== auth()->id())
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash"
                                            class="text-danger-600 hover:text-danger-700 hover:bg-danger-50"
                                            wire:click="openDeleteModal({{ $user->id }})"
                                            aria-label="{{ __('Eliminar usuario') }}"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <div class="mx-auto h-12 w-12 text-gray-300 mb-4 flex items-center justify-center">
                                        <flux:icon.users class="w-12 h-12" />
                                    </div>
                                    @if($search)
                                        <p class="text-sm">{{ __('No se encontraron usuarios que coincidan con tu búsqueda.') }}</p>
                                    @else
                                        <p class="text-sm">{{ __('No hay usuarios registrados aún.') }}</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Users count -->
    <div class="text-sm text-gray-500">
        {{ __('Mostrando :from-:to de :total usuarios', [
            'from' => $users->firstItem() ?? 0,
            'to' => $users->lastItem() ?? 0,
            'total' => $users->total()
        ]) }}
    </div>

    <!-- Create User Modal -->
   @include('components.settings.create-user-modal')

    <!-- Edit User Modal -->
    @include('components.settings.edit-user-modal', ['selectedUser' => $selectedUser])

    <!-- View User Modal -->
    @include('components.settings.view-user-modal', ['selectedUser' => $selectedUser])

    <!-- Delete User Modal -->
    @include('components.settings.delete-user-modal', ['selectedUser' => $selectedUser])
</div>