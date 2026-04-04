<?php

namespace App\Livewire\Resources\Products;

use App\Services\ProductService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class CreateProductModal extends Component
{
    public bool $showCreateModal = false;

    public string $name = '';
    public string $description = '';

    protected ProductService $productService;

    protected $listeners = [
        'open-create-product-modal' => 'openCreateProductModal',
    ];

    public function boot()
    {
        $this->productService = app(ProductService::class);
    }

    public function openCreateProductModal()
    {
        $this->showCreateModal = true;
        $this->reset(['name', 'description']);
        $this->resetValidation();
    }

    public function createProduct()
    {
        try {
            $result = $this->productService->createProduct($this->getFormData());

            if ($result['success']) {
                $this->resetValidation();
                $this->dispatch('products-info-updated', $result);
                $this->showCreateModal = false;
                return;
            }

            if (($result['type'] ?? 'error') === 'validation') {
                $this->setErrorBag(new MessageBag($result['errors'] ?? []));
                return;
            }

            $this->dispatch('product-creation-failed', $result['message']);
        } catch (\Exception $e) {
            $this->dispatch('product-creation-failed', $e->getMessage());
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
        return view('livewire.resources.products.create-product-modal');
    }
}
