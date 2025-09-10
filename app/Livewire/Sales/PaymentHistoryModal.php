<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class PaymentHistoryModal extends Component
{
    public bool $showPaymentHistoryModal = false;
    public ?Sale $selectedSale = null;

    public $listeners = [
        'open-payment-history-modal' => 'openPaymentHistoryModal',
    ];

    public function mount() {}

    public function openAddPaymentModal()
    {
        $this->showPaymentHistoryModal = false;
        $this->dispatch('open-add-payment-modal', $this->selectedSale->id);
    }

    public function openPaymentHistoryModal(int $saleId)
    {
        $this->showPaymentHistoryModal = true;
        $this->selectedSale = Sale::with(['payments.user', 'payments.route', 'productList'])->findOrFail($saleId);
        session()->forget(['error', 'message']);
    }

    public function render()
    {
        return view('livewire.sales.payment-history-modal');
    }
}
