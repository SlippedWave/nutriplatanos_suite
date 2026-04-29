<?php

namespace App\Livewire\Resources\Products;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\MessageBag;
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
        $this->resetValidation();
        $this->showUpdateModal = true;
    }

    public function updateProduct()
    {
        try {
            $response = $this->productService->updateProduct($this->selectedProductId, $this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Producto actualizado exitosamente'
                : 'Error al actualizar producto');  
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
                $this->showUpdateModal = false;
                return;
            }

             if (($type ?? 'exception') === 'validation-exception') {

                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }
        
            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al actualizar producto: ' . $e->getMessage(),
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
        return view('livewire.resources.products.update-product-modal');
    }
}
