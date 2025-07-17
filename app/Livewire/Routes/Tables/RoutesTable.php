<?php

namespace App\Livewire\Routes\Tables;

use App\Models\Route;
use App\Models\User;
use App\Services\RouteService;
use Livewire\Component;
use Livewire\WithPagination;

class RoutesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $includeDeleted = false;
    public $statusFilter = '';
    public $carrierFilter = '';

    // Modal states
    public bool $showCreateModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;
    public bool $showEditRouteModal = false;
    public bool $showCloseRouteModal = false;

    // Form fields
    public $title = '';
    public $carrier_id = '';
    public $status = 'active';
    public $notes = '';

    public ?Route $selectedRoute = null;

    protected RouteService $routeService;

    public function boot(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'carrierFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedCarrierFilter()
    {
        $this->resetPage();
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
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
        $this->resetPage();
    }

    // Modal management methods
    public function openCreateModal()
    {
        $this->resetFormFields();
        $this->showCreateModal = true;
    }

    public function openEditModal($routeId)
    {
        $this->selectedRoute = Route::findOrFail($routeId);
        $this->fillForm($this->selectedRoute);
        $this->showEditRouteModal = true;
    }

    public function openViewModal($routeId)
    {
        $this->selectedRoute = Route::withTrashed()->findOrFail($routeId);
        $this->showViewModal = true;
    }

    public function openDeleteModal($routeId)
    {
        $this->selectedRoute = Route::findOrFail($routeId);
        $this->showDeleteModal = true;
    }

    public function openCloseModal($routeId)
    {
        $this->selectedRoute = Route::findOrFail($routeId);
        $this->showCloseRouteModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditRouteModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->showCloseRouteModal = false;
        $this->selectedRoute = null;
        $this->resetFormFields();
    }

    // CRUD operations using RouteService
    public function createRoute()
    {
        $result = $this->routeService->createRoute($this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function updateRoute()
    {
        if (!$this->selectedRoute) {
            session()->flash('error', 'No se ha seleccionado ninguna ruta.');
            return;
        }

        $result = $this->routeService->editRoute($this->selectedRoute, $this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function deleteRoute()
    {
        if (!$this->selectedRoute) {
            session()->flash('error', 'No se ha seleccionado ninguna ruta.');
            return;
        }

        $result = $this->routeService->deleteRoute($this->selectedRoute);

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function closeRoute()
    {
        if (!$this->selectedRoute) {
            session()->flash('error', 'No se ha seleccionado ninguna ruta.');
            return;
        }

        $result = $this->routeService->closeRoute($this->selectedRoute);

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    // Utility methods
    private function getFormData(): array
    {
        return [
            'title' => $this->title,
            'carrier_id' => $this->carrier_id,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }

    private function resetFormFields()
    {
        $this->title = '';
        $this->carrier_id = auth()->user()->role === 'carrier' ? auth()->id() : '';
        $this->status = 'active';
        $this->notes = '';
    }

    private function fillForm(Route $route)
    {
        $this->title = $route->title ?? '';
        $this->carrier_id = $route->carrier_id;
        $this->status = $route->status;
        $this->notes = '';
    }

    public function render()
    {
        $carriers = collect();

        // Only admins and coordinators can see all carriers
        if (in_array(auth()->user()->role, ['admin', 'coordinator'])) {
            $carriers = User::query()->get();
        }

        return view('livewire.routes.tables.routes-table', [
            'routes' => $this->routeService->searchRoutes(
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage,
                $this->includeDeleted,
                $this->statusFilter ?: null,
                $this->carrierFilter ?: null
            ),
            'carriers' => $carriers
        ]);
    }
}
