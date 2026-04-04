<?php

namespace App\Livewire\Resources\Products;

use App\Models\Product;
use App\Services\ProductService;
use Livewire\Component;

class UpdateProductModal extends Component
{
    public bool $showUpdateModal = false;

    public ?int $selectedProductId = null;

    public string $name = '';
    public string $description = '';

    protected ProductService $productService;

    protected $listeners = [
        'open-update-product-modal' => 'openUpdateProductModal',
    ];

    public function boot()
    {
        $this->productService = app(ProductService::class);
    }

    public function openUpdateProductModal($id)
    {
        $product = Product::findOrFail($id);
        $this->selectedProductId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->showUpdateModal = true;
    }

    public function updateProduct()
    {
        try {
            $result = $this->productService->updateProduct($this->selectedProductId, $this->getFormData());
            $this->dispatch('products-info-updated', $result);
            $this->showUpdateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('product-update-failed', $e->getMessage());
        }
    }

    public function getFormData()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function render()
    {
        return view('livewire.resources.products.update-product-modal');
    }
}
