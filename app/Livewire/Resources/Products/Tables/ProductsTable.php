<?php

namespace App\Livewire\Resources\Products\Tables;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsTable extends Component
{
    use WithPagination;

    public $perPage = 3;
    public $sortField = 'name';
    public $sortDirection = 'desc';

    public function updatePerPage($perPage)
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    protected $listeners = [
        'products-info-updated' => '$refresh',
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.resources.products.tables.products-table', compact('products'));
    }
}
