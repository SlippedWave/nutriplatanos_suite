<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Support\MessageBag;
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
            $response = $this->customerService->updateCustomer($this->selectedCustomer, $this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Cliente actualizado exitosamente'
                : 'Error al actualizar cliente');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'customers-table',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('customers-info-updated');
                $this->showUpdateModal = false;
                return;
            }

            if (($response['type'] ?? 'error') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }   

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al actualizar cliente',
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'customers-table',
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
        return view('livewire.customers.update-customer-modal');
    }
}
