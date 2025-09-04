<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class ViewSaleModal extends Component
{
    public bool $showViewModal = false;
    public ?Sale $selectedSale = null;

    public $listeners = [
        'open-view-sale-modal' => 'openViewModal',
    ];

    public function mount() {}

    public function openViewModal(int $saleId)
    {
        $this->showViewModal = true;
        $this->selectedSale = Sale::with(['saleDetails.product', 'customer', 'route', 'user'])
            ->withTrashed()
            ->findOrFail($saleId);
        session()->forget(['error', 'message']);
    }

    public function render()
    {
        return view('livewire.sales.view-sale-modal');
    }
}
