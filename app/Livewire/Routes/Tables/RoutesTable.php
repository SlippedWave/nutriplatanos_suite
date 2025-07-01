<?php

namespace App\Livewire\Routes\Tables;

use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

class RoutesTable extends Component
{
    use WithPagination;

    public $user_id = null;
    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $dateFilter = 'all'; // all, today, week, month
    public $statusFilter = 'all'; // all, or specific status
    public $startDate = null;
    public $endDate = null;

    // Modal states
    public bool $showCreateModal = false;
    public bool $showUpdateModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;

    // Form fields
    public $name = '';
    public $description = '';
    public $active = true;

    public ?Route $selectedRoute = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'dateFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount($user_id = null)
    {
        $this->user_id = $user_id;
        $this->applyDateFilter();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->applyDateFilter();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    private function applyDateFilter()
    {
        $now = now();

        switch ($this->dateFilter) {
            case 'today':
                $this->startDate = $now->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                break;
            case 'week':
                $this->startDate = $now->startOfWeek()->toDateString();
                $this->endDate = $now->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->toDateString();
                $this->endDate = $now->endOfMonth()->toDateString();
                break;
            default:
                $this->startDate = null;
                $this->endDate = null;
        }
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

    public function openViewModal($routeId)
    {
        $this->selectedRoute = Route::with('carrier')->find($routeId);
        $this->showViewModal = true;
    }

    public function openDeleteModal($routeId)
    {
        $this->selectedRoute = Route::find($routeId);
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showUpdateModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->selectedRoute = null;
        $this->resetFormFields();
    }

    private function resetFormFields()
    {
        $this->name = '';
        $this->description = '';
        $this->active = true;
    }

    public function deleteRoute()
    {
        if ($this->selectedRoute) {
            $this->selectedRoute->delete();
            $this->closeModals();
            session()->flash('message', 'Ruta eliminada correctamente.');
        }
    }

    public function render()
    {
        $query = Route::query()
            ->with('carrier') // Eager load the carrier relationship
            ->when($this->user_id, function ($query) {
                return $query->where('carrier_id', $this->user_id);
            })
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->whereHas('carrier', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->startDate, function ($query) {
                return $query->whereDate('date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                return $query->whereDate('date', '<=', $this->endDate);
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                return $query->where('status', $this->statusFilter);
            });

        // Handle sorting
        if ($this->sortField === 'carrier_name') {
            $query->orderByCarrierName($this->sortDirection);
        } elseif ($this->sortField === 'route_status') {
            $query->orderBy('status', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $routes = $query->paginate($this->perPage);

        return view('livewire.routes.tables.routes-table', [
            'routes' => $routes
        ]);
    }
}
