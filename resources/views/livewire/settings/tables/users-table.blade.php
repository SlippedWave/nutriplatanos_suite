<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // Modal states
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $curp = '';
    public string $rfc = '';
    public string $address = '';
    public string $emergency_contact = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relationship = '';
    public string $role = 'carrier';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    // Selected user for operations
    public ?User $selectedUser = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->fillForm($this->selectedUser);
        $this->showEditModal = true;
    }

    public function openViewModal($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showViewModal = true;
    }

    public function openDeleteModal($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->selectedUser = null;
    }

    public function createUser()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'curp' => ['required', 'string', 'max:18'],
            'rfc' => ['required', 'string', 'max:13'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:admin,carrier,coordinator'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        $this->closeModals();
        $this->dispatch('user-created');
        session()->flash('message', 'Usuario creado exitosamente.');
    }

    public function updateUser()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->selectedUser->id)],
            'phone' => ['required', 'string', 'max:20'],
            'curp' => ['required', 'string', 'max:18'],
            'rfc' => ['required', 'string', 'max:13'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:admin,carrier,coordinator'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($this->password)) {
            $this->validate([
                'password' => ['string', 'min:8', 'confirmed'],
            ]);
            $validated['password'] = Hash::make($this->password);
        }

        $this->selectedUser->update($validated);

        $this->closeModals();
        $this->dispatch('user-updated');
        session()->flash('message', 'Usuario actualizado exitosamente.');
    }

    public function deleteUser()
    {
        if ($this->selectedUser->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return;
        }

        $this->selectedUser->delete();

        $this->closeModals();
        $this->dispatch('user-deleted');
        session()->flash('message', 'Usuario eliminado exitosamente.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->curp = '';
        $this->rfc = '';
        $this->address = '';
        $this->emergency_contact = '';
        $this->emergency_contact_phone = '';
        $this->emergency_contact_relationship = '';
        $this->role = 'carrier';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
    }

    private function fillForm(User $user)
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->curp = $user->curp ?? '';
        $this->rfc = $user->rfc ?? '';
        $this->address = $user->address ?? '';
        $this->emergency_contact = $user->emergency_contact ?? '';
        $this->emergency_contact_phone = $user->emergency_contact_phone ?? '';
        $this->emergency_contact_relationship = $user->emergency_contact_relationship ?? '';
        $this->role = $user->role;
        $this->is_active = $user->is_active;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function with()
    {
        return [
            'users' => User::query()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
        ];
    }
}; ?>

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