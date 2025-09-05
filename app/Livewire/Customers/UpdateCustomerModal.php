<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;

class UpdateCustomerModal extends Component
{
    public bool $showUpdateModal = false;

    public ?Customer $selectedCustomer = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $rfc = '';
    public $active = true;
    public $notes = '';

    protected CustomerService $customerService;

    protected $listeners = [
        'open-update-customer-modal' => 'openUpdateCustomerModal'
    ];

    public function boot()
    {
        $this->customerService = app(CustomerService::class);
    }

    public function openUpdateCustomerModal(int $customerId)
    {
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->name = $this->selectedCustomer->name;
        $this->email = $this->selectedCustomer->email;
        $this->phone = $this->selectedCustomer->phone ?? '';
        $this->address = $this->selectedCustomer->address ?? '';
        $this->rfc = $this->selectedCustomer->rfc ?? '';
        $this->active = $this->selectedCustomer->active;

        $this->showUpdateModal = true;
    }

    public function updateCustomer()
    {
        try {
            $result = $this->customerService->updateCustomer($this->selectedCustomer, $this->getFormData());
            $this->dispatch('customers-info-updated');
            $this->dispatch('show-customers-table-message', $result);
            $this->showUpdateModal = false;
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
        return view('livewire.customers.update-customer-modal');
    }
}
