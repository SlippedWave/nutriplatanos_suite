<?php

namespace App\Livewire\Settings\Users;

use App\Services\UserService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class CreateUserModal extends Component
{
    public bool $showCreateModal = false;

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
        'open-create-user-modal' => 'openCreateUserModal'
    ];

    public function boot()
    {
        $this->userService = app(UserService::class);
    }

    public function openCreateUserModal()
    {
        $this->reset([
            'name', 'email', 'phone', 'curp', 'rfc', 'address',
            'emergency_contact', 'emergency_contact_phone', 'emergency_contact_relationship',
            'role', 'password', 'password_confirmation', 'active', 'notes'
        ]);
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function createUser()
    {
        try {
            $response = $this->userService->createUser($this->getFormData());

            $success = $response['success'] ?? false;

            $message = $response['message'] ?? ($success
                ? 'Usuario creado exitosamente'
                : 'Error al crear usuario');
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
                $this->showCreateModal = false;
                return;
            }

            if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }   
 
            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Creación de usuario fallida: ' . $e->getMessage(),
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
        return view('livewire.settings.users.create-user-modal');
    }
}
