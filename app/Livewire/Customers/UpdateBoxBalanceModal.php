<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\BoxBalanceService;
use Livewire\Component;
use Livewire\Attributes\On;

class UpdateBoxBalanceModal extends Component
{
    public bool $showUpdateModal = false;

    public int $customerId;
    public Customer $customer;

    public int $box_balance_delivered = 0;
    public int $box_balance_returned = 0;

    protected BoxBalanceService $boxBalanceService;

    public function boot()
    {
        $this->boxBalanceService = app(BoxBalanceService::class);
    }

    #[On('open-update-box-balance-modal')]
    public function openUpdateModal(int $customerId): void
    {
        $this->resetValidation();
        $this->customerId = $customerId;
        $this->customer = Customer::find($customerId);
        $this->showUpdateModal = true;
    }

    public function updateBoxBalance()
    {
        try {
            $response = $this->boxBalanceService->updateBoxBalance(
                customer_id: $this->customerId,
                box_balance_delivered: $this->box_balance_delivered ?? 0,
                box_balance_returned: $this->box_balance_returned ?? 0
            );

            dump($response);
    
            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success 
                ? 'Balance de cajas actualizado exitosamente' 
                : 'Error al actualizar balance de cajas');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');
    
            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);

            if ($success) {
                $this->dispatch('box-balance-updated');
                $this->showUpdateModal = false;
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al actualizar balance de cajas: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'customers',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.customers.update-box-balance-modal');
    }
}
