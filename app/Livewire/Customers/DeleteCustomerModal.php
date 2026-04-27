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
            $response = $this->customerService->deleteCustomer($this->selectedCustomer);

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Cliente eliminado exitosamente'
                : 'Error al eliminar cliente');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);

            if ($success) {
                $this->dispatch('customers-info-updated');
                $this->showDeleteModal = false;
                return;
            }  

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al eliminar cliente: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.customers.delete-customer-modal');
    }
}
