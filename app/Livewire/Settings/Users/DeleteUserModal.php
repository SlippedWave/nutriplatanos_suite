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
            $response = $this->userService->deleteUser($this->selectedUser, Auth::user());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Usuario eliminado exitosamente'
                : 'Error al eliminar usuario');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'users-table',
            ]);

            if ($success) {
                $this->dispatch('users-info-updated');
                $this->showDeleteModal = false;
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al eliminar usuario',
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'users-table',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.settings.users.delete-user-modal');
    }
}
