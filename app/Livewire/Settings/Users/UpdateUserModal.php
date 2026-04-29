<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class UpdateUserModal extends Component
{
    public bool $showUpdateModal = false;

    public ?User $selectedUser = null;

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

    protected UserService $userService;

    protected $listeners = [
        'open-update-user-modal' => 'openUpdateUserModal'
    ];

    public function boot()
    {
        $this->userService = app(UserService::class);
    }

    public function openUpdateUserModal(int $userId)
    {
        $this->resetValidation();
        $this->selectedUser = User::findOrFail($userId);
        $this->name = $this->selectedUser->name;
        $this->email = $this->selectedUser->email;
        $this->phone = $this->selectedUser->phone ?? '';
        $this->curp = $this->selectedUser->curp ?? '';
        $this->rfc = $this->selectedUser->rfc ?? '';
        $this->address = $this->selectedUser->address ?? '';
        $this->emergency_contact = $this->selectedUser->emergency_contact ?? '';
        $this->emergency_contact_phone = $this->selectedUser->emergency_contact_phone ?? '';
        $this->emergency_contact_relationship = $this->selectedUser->emergency_contact_relationship ?? '';
        $this->role = $this->selectedUser->role;
        $this->active = $this->selectedUser->active;

        $this->password = '';
        $this->password_confirmation = '';
        $this->notes = '';

        $this->showUpdateModal = true;
    }

    public function updateUser()
    {
        try {
            $response = $this->userService->updateUser($this->selectedUser, $this->getFormData());

            $success = $response['success'] ?? false;

            $message = $response['message'] ?? ($success
                ? 'Usuario actualizado exitosamente'
                : 'Error al actualizar usuario');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'users',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('users-info-updated');
                $this->showUpdateModal = false;
                return;
            }

             if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al actualizar usuario: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'users',
            ]);
        }
    }

    public function getFormData()
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

    public function render()
    {
        return view('livewire.settings.users.update-user-modal');
    }
}
