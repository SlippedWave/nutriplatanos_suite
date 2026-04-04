<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;

class DeleteCustomerModal extends Component
{
    public bool $showDeleteModal = false;

    public ?Customer $selectedCustomer = null;

    protected CustomerService $customerService;

    protected $listeners = [
        'open-delete-customer-modal' => 'openDeleteCustomerModal'
    ];

    public function boot()
    {
        $this->customerService = app(CustomerService::class);
    }

    public function openDeleteCustomerModal(int $customerId)
    {
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->showDeleteModal = true;
    }

    public function deleteCustomer()
    {
        try {
            $result = $this->customerService->deleteCustomer($this->selectedCustomer);
            $this->dispatch('customers-info-updated');
            $this->dispatch('show-customers-table-message', $result);
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-customers-table-message', $result);
        }
    }

    public function render()
    {
        return view('livewire.customers.delete-customer-modal');
    }
}
