<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\BoxBalanceAdjustmentService;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateBoxBalanceAdjustmentModal extends Component
{
    public bool $showModal = false;

    public int $customerId;
    public Customer $customer;

    public int $quantity = 0;
    public string $reason = '';

    protected BoxBalanceAdjustmentService $service;

    public function boot(): void
    {
        $this->service = app(BoxBalanceAdjustmentService::class);
    }

    #[On('open-box-balance-adjustment-modal')]
    public function openModal(int $customerId): void
    {
        $this->resetValidation();
        $this->customerId = $customerId;
        $this->customer   = Customer::findOrFail($customerId);
        $this->quantity   = 0;
        $this->reason     = '';
        $this->showModal  = true;
    }

    public function save(): void
    {
        $response = $this->service->createAdjustment([
            'customer_id' => $this->customerId,
            'quantity'    => $this->quantity,
            'reason'      => $this->reason ?: null,
        ]);

        $success = $response['success'] ?? false;
        $type    = $success ? 'success' : ($response['type'] ?? 'exception');

        $this->dispatch('show-message-banner', [
            'text'     => $response['message'],
            'type'     => $type,
            'duration' => 5000,
            'bannerId' => 'customers',
        ]);

        if (!$success) {
            if (isset($response['validation-errors'])) {
                foreach ($response['validation-errors'] as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }
            }
            return;
        }

        $this->dispatch('box-balance-adjustment-saved');
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.customers.create-box-balance-adjustment-modal');
    }
}
