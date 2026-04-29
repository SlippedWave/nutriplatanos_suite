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
            $response = $this->productService->createProduct($this->getFormData());

            $success = $response['success'] ?? false;

            $message = $response['message'] ?? ($success
                ? 'Producto creado exitosamente'
                : 'Error al crear producto');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'products',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('products-info-updated');
                $this->showCreateModal = false;
                return;
            }

            if (($type ?? 'exception') === 'validation-exception') {
                 $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                 return;
            }   
    
            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Creación de producto fallida: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'products',
            ]);     
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
