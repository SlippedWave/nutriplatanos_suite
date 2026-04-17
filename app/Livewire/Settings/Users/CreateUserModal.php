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

            if ($response['success']) {
                $this->resetValidation();
                $this->dispatch('users-info-updated');
                $this->dispatch('show-users-table-success-message', [
                    'message' => $response['message'] ?? 'Creación de usuario exitosa',
                ]);
                $this->showCreateModal = false;
                return;
            }

            if (($response['type'] ?? 'error') === 'validation') {
                $this->setErrorBag(new MessageBag($response['errors'] ?? []));  
                $this->dispatch('show-users-table-error-message', [
                    'message' => 'Creación de usuario fallida',
                    'validation-errors' => $response['errors'] ?? [],
                    'error-type' => 'validation',
                ]);
                return;
            }   

            $this->dispatch('show-users-table-error-message', [
                'message' => $response['message'] ?? 'Creación de usuario fallida',
                'error-type' => 'error',
            ]);
                
        } catch (\Exception $e) {
            $this->dispatch('show-users-table-error-message', [
                'message' => 'Creación de usuario fallida',
                'error-type' => 'error',
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
