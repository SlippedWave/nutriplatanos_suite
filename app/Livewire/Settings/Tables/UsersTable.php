<?php

namespace App\Livewire\Settings\Tables;

use App\Models\User;
use App\Models\Note;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UsersTable extends Component
{
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
    public bool $active = true;

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
            'active' => ['boolean'],
            'notes' => ['nullable|string|max:1000'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($validated['notes']) {
            Note::create([
                'user_id' => auth()->user()->id,
                'content' => $validated['notes'],
                'type' => 'user',
                'notable_id' => $user->id,
                'notable_type' => User::class,
            ]);

            $this->dispatch('note-created');
        }

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
            'active' => ['boolean'],
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
        if ($this->selectedUser->id === auth()->user()->id) {
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
        $this->active = true;
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
        $this->active = $user->active;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function render()
    {
        return view('livewire.settings.tables.users-table', [
            'users' => User::query()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
        ]);
    }
}
