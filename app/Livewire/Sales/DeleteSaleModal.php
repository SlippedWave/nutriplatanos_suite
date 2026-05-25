<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Services\SaleService;
use Livewire\Component;

class DeleteSaleModal extends Component
{
    public bool $showDeleteModal = false;

    public ?Sale $selectedSale = null;

    protected SaleService $saleService;

    public $listeners = [
        'open-delete-sale-modal' => 'openDeleteModal',
    ];

    public function boot()
    {
        $this->saleService = app(SaleService::class);
    }

    public function openDeleteModal(int $saleId)
    {
        $this->selectedSale = Sale::findOrFail($saleId);        
        $this->showDeleteModal = true;
    }

    public function deleteSale()
    {
        if (!$this->selectedSale) {
            $this->dispatch(
                'show-message-banner',
                [
                    'text' => 'No se ha seleccionado ninguna venta.',
                    'type' => 'exception',
                    'duration' => 5000,
                    'bannerId' => 'sales',
                ]
            );
            return;
        }

        try {
            $response = $this->saleService->deleteSale($this->selectedSale);

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Venta eliminada exitosamente'
                : 'Error al eliminar la venta');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'sales',
            ]);
            
            if ($success) {

                $this->dispatch('sales-info-updated');
                $this->showDeleteModal = false;
                return;
            }

        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al eliminar la venta: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'sales',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.sales.delete-sale-modal');
    }
}
