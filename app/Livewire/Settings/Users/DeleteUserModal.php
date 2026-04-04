<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserModal extends Component
{
    public bool $showDeleteModal = false;

    public ?User $selectedUser = null;

    protected UserService $userService;

    protected $listeners = [
        'open-delete-user-modal' => 'openDeleteUserModal'
    ];

    public function boot()
    {
        $this->userService = app(UserService::class);
    }

    public function openDeleteUserModal(int $userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        try {
            $result = $this->userService->deleteUser($this->selectedUser, Auth::user());
            $this->dispatch('users-info-updated');
            $this->dispatch('show-users-table-message', $result);
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-users-table-message', $result);
        }
    }

    public function render()
    {
        return view('livewire.settings.users.delete-user-modal');
    }
}
