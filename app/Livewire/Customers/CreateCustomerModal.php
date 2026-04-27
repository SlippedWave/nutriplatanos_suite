<?php

namespace App\Livewire\Customers;

use App\Services\CustomerService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class CreateCustomerModal extends Component
{
    public bool $showCreateModal = false;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $rfc = '';
    public $active = true;
    public $notes = '';

    protected CustomerService $customerService;

    protected $listeners = [
        'open-create-customer-modal' => 'openCreateCustomerModal'
    ];

    public function boot()
    {
        $this->customerService = app(CustomerService::class);
    }

    public function openCreateCustomerModal()
    {
        $this->resetValidation();
        $this->reset([
                'name', 'email', 'phone', 'address', 'rfc', 'active', 'notes'
        ]);
        $this->showCreateModal = true;
    }

    public function createCustomer()
    {
        try {
            $response = $this->customerService->createCustomer($this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Cliente creado exitosamente'
                : 'Error al crear cliente');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);
            
            if ($success) {
                $this->resetValidation();
                $this->dispatch('customers-info-updated');
                $this->showCreateModal = false;
                return;
            } 

            if (($type ?? 'error') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }   

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Creación de cliente fallida: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);
        }
    }

    private function getFormData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'rfc' => $this->rfc,
            'active' => $this->active,
            'notes' => $this->notes,
        ];
    }

    public function render()
    {
        return view('livewire.customers.create-customer-modal');
    }
}
