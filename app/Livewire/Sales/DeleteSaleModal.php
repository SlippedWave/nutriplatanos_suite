<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Services\SaleService;
use Livewire\Component;

class DeleteSaleModal extends Component
{
    public bool $showDeleteModal = false;
    public ?Sale $selectedSale = null;

    public $listeners = [
        'open-delete-sale-modal' => 'openDeleteModal',
    ];

    protected SaleService $saleService;

    public function boot()
    {
        $this->saleService = app(SaleService::class);
    }

    public function openDeleteModal(int $saleId)
    {
        $this->showDeleteModal = true;
        $this->selectedSale = Sale::findOrFail($saleId);
        session()->forget(['error', 'message']);
    }

    public function deleteSale()
    {
        if (!$this->selectedSale) {
            $this->dispatch(
                'flash-sales-table-message',
                'No se ha seleccionado ninguna venta.',
                'error'
            );
            return;
        }

        $result = $this->saleService->deleteSale($this->selectedSale);

        if ($result['success']) {
            $this->showDeleteModal = false;
            $this->dispatch('refresh-sales-table');
            $this->dispatch('show-sales-table-message', $result);
        } else {
            // Handle different types of errors
            switch ($result['type'] ?? 'error') {
                case 'validation':
                    // Don't close modal for validation errors
                    if (isset($result['errors'])) {
                        // Set validation errors for display
                        foreach ($result['errors'] as $field => $messages) {
                            $this->addError($field, implode(' ', $messages));
                        }
                    }
                    $this->dispatch(
                        'flash-sales-table-message',
                        $result['message'],
                        'error'
                    );
                    break;
                default:
                    $this->dispatch('show-sales-table-message', $result);
                    break;
            }
        }
    }

    public function render()
    {
        return view('livewire.sales.delete-sale-modal');
    }
}
