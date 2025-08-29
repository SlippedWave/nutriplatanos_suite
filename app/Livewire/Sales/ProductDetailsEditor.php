<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class ProductDetailsEditor extends Component
{
    #[Modelable]
    public array $saleProducts = [];

    public $products = [];

    public $listeners = [
        'add-product' => 'addProduct',
    ];

    public function mount()
    {
        $this->products = Product::all();
    }

    public function addProduct()
    {
        $this->saleProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'price_per_unit' => 0,
        ];
    }

    public function removeProduct($index)
    {
        if (count($this->saleProducts) > 1) {
            unset($this->saleProducts[$index]);
            $this->saleProducts = array_values($this->saleProducts); // Re-index array
        }
    }

    public function render()
    {
        return view('livewire.sales.product-details-editor');
    }
}
