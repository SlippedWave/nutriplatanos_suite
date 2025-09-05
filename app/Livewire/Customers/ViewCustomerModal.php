<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class ViewCustomerModal extends Component
{
    public bool $showViewModal = false;

    public ?Customer $selectedCustomer = null;

    protected $listeners = [
        'open-view-customer-modal' => 'openViewCustomerModal'
    ];

    public function openViewCustomerModal(int $customerId)
    {
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->showViewModal = true;
    }

    public function render()
    {
        return view('livewire.customers.view-customer-modal');
    }
}
