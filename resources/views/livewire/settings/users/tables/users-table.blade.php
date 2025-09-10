<div class="space-y-6">
    <!-- Flash Messages -->
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'users-table')
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

    <div class="flex flex-col gap-4">
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 sm:p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Buscar usuarios...') }}"
                    />
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <flux:button 
                        variant="primary" 
                        wire:click="toggleIncludeDeleted"
                        class="{{ $includeDeleted ? 'bg-gray-100! text-gray-900!' : 'bg-background! text-gray-500! hover:bg-gray-50!' }}" 
                        aria-label="{{ $includeDeleted ? __('Ocultar usuarios eliminados') : __('Incluir usuarios eliminados') }}"
                    >
                        {{ $includeDeleted ? __('Ocultar eliminados') : __('Incluir eliminados') }}
                    </flux:button>
                    <flux:select wire:model.live="perPage" class="w-20">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </flux:select>
                    <flux:button 
                        variant="primary" 
                        icon="plus"
                        wire:click="$dispatch('open-create-user-modal')"
                    >
                        {{ __('Nuevo Usuario') }}
                    </flux:button>
                </div>
            </div>
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
                                    @if ($user->trashed())
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            wire:click="$dispatch('open-view-user-modal', { userId: {{ $user->id }} })"
                                            aria-label="{{ __('Ver usuario') }}"
                                        />

                                        <div class="mt-2">
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-danger-100 text-danger-800">
                                                {{ __('Usuario eliminado') }}
                                            </span>
                                        </div>
                                    @else
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil"
                                            wire:click="$dispatch('open-update-user-modal', { userId: {{ $user->id }} })"
                                            aria-label="{{ __('Editar usuario') }}"
                                        />
                                        
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            wire:click="$dispatch('open-view-user-modal', { userId: {{ $user->id }} })"
                                            aria-label="{{ __('Ver usuario') }}"
                                        />
                                        
                                        @if($user->id !== auth()->id())
                                            <flux:button 
                                                variant="ghost" 
                                                size="sm" 
                                                icon="trash"
                                                class="text-danger-600 hover:text-danger-700 hover:bg-danger-50"
                                                wire:click="$dispatch('open-delete-user-modal', { userId: {{ $user->id }} })"
                                                aria-label="{{ __('Eliminar usuario') }}"
                                            />
                                        @endif
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

    <livewire:settings.users.create-user-modal />
    <livewire:settings.users.update-user-modal />
    <livewire:settings.users.view-user-modal />
    <livewire:settings.users.delete-user-modal />
</div>