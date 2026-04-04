<?php

namespace App\Livewire\Customers;

use App\Services\CustomerService;
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
        $this->reset([
                'name', 'email', 'phone', 'address', 'rfc', 'active', 'notes'
        ]);
        $this->showCreateModal = true;
    }

    public function createCustomer()
    {
        try {
            $result = $this->customerService->createCustomer($this->getFormData());
            $this->dispatch('customers-info-updated');
            $this->dispatch('show-customers-table-message', $result);
            $this->showCreateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-customers-table-message', $result);
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
