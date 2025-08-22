<?php

namespace App\Livewire\Settings\Tables;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public bool $includeDeleted = false;

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
    public string $notes = '';

    // Selected user for operations
    public ?User $selectedUser = null;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    protected function showUsersTableMessage($result)
    {
        $this->closeModals();
        $this->flashUsersTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    protected function flashUsersTableMessage(string $text, string $type = 'success'): void
    {
        session()->flash('message', [
            'header' => 'users-table',
            'text' => $text,
            'type' => $type,
        ]);
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
        $this->resetPage();
    }

    // Modal management methods
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
        $this->selectedUser = User::withTrashed()->findOrFail($userId);
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
        $this->selectedUser = null;
        $this->resetForm();
    }

    // CRUD operations
    public function createUser()
    {
        $result = $this->userService->createUser($this->getFormData());

        $this->showUsersTableMessage($result);
    }

    public function updateUser()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'No se ha seleccionado ningún usuario.');
            return;
        }

        $result = $this->userService->updateUser($this->selectedUser, $this->getFormData());

        $this->showUsersTableMessage($result);
    }

    public function deleteUser()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'No se ha seleccionado ningún usuario.');
            return;
        }

        $result = $this->userService->deleteUser($this->selectedUser, Auth::user());

        $this->showUsersTableMessage($result);
    }

    // Utility methods
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    private function getFormData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'curp' => $this->curp,
            'rfc' => $this->rfc,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'role' => $this->role,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'active' => $this->active,
            'notes' => $this->notes,
        ];
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
        $this->notes = '';
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
        // Don't fill password fields for security
        $this->password = '';
        $this->password_confirmation = '';
        $this->notes = '';
    }

    public function render()
    {
        return view('livewire.settings.tables.users-table', [
            'users' => $this->userService->searchUsers(
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage,
                $this->includeDeleted
            )
        ]);
    }
}
