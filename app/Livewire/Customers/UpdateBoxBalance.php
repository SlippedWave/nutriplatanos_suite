<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\BoxBalanceService;
use Livewire\Component;
use Livewire\Attributes\On;

class UpdateBoxBalance extends Component
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
        $this->customerId = $customerId;
        $this->customer = Customer::find($customerId);
        $this->showUpdateModal = true;
    }

    public function updateBoxBalance()
    {
        $result = $this->boxBalanceService->updateBoxBalance(
            customer_id: $this->customerId,
            box_balance_delivered: $this->box_balance_delivered ?? 0,
            box_balance_returned: $this->box_balance_returned ?? 0
        );

        if ($result['success']) {
            $this->reset();
            $this->showUpdateModal = false;
            session()->flash('message', $result['message']);
            $this->dispatch('box-balance-uploaded');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.customers.update-box-balance');
    }
}
