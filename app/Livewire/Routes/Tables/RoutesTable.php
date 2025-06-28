<?php

namespace App\Livewire\Routes\Tables;

use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

class RoutesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Modal states
    public bool $showCreateModal = false;
    public bool $showUpdateModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;

    // Form fields
    public $name = '';
    public $description = '';
    public $is_active = true;

    public ?Route $selectedRoute = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->selectedRoute = null;
        $this->resetFormFields();
        $this->showCreateModal = true;
    }

    private function resetFormFields()
    {
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }

    // Other methods for creating, updating, deleting, and viewing routes would go here

    public function render()
    {
        return view('livewire.routes.tables.routes-table', [
            'routes' => Route::query()
                ->when($this->search, function ($query) {
                    return $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage),
        ]);
    }
}
