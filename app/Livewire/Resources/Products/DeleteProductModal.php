<?php

namespace App\Livewire\Resources\Products;

use App\Models\Product;
use App\Services\ProductService;
use Livewire\Component;

class DeleteProductModal extends Component
{
    public bool $showDeleteModal = false;
    public ?Product $selectedProduct = null;

    protected ProductService $productService;

    protected $listeners = [
        'open-delete-product-modal' => 'openDeleteProductModal',
    ];

    public function boot()
    {
        $this->productService = app(ProductService::class);
    }

    public function openDeleteProductModal(int $id)
    {
        $this->selectedProduct = Product::find($id);
        $this->showDeleteModal = true;
    }

    public function deleteProduct(): void
    {
        try {
            $response = $this->productService->deleteProduct($this->selectedProduct->id);

            $success = $response['success'] ?? false;

            $message = $response['message'] ?? ($success
                ? 'Producto eliminado exitosamente'
                : 'Error al eliminar producto');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'products',
            ]);

            if ($success) {
                $this->dispatch('products-info-updated');
                $this->showDeleteModal = false;
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Eliminación de producto fallida: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'products',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.resources.products.delete-product-modal');
    }
}
