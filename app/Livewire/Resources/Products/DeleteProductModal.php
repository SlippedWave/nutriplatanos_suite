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
            if ($this->selectedProduct) {
                $this->productService->deleteProduct($this->selectedProduct->id);
                $this->dispatch('products-info-updated');
                $this->showDeleteModal = false;
            }
        } catch (\Exception $e) {
            $this->dispatch('product-deletion-failed', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.resources.products.delete-product-modal');
    }
}
