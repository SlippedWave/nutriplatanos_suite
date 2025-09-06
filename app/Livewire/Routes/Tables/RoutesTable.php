<?php

namespace App\Livewire\Routes\Tables;

use App\Models\Route;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

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

    protected $listeners = [
        'routes-info-updated' => '$refresh',
        'show-routes-table-message' => 'showRoutesTableMessage',
        'flash-routes-table-message' => 'flashRoutesTableMessage',
    ];


    public ?Route $selectedRoute = null;

    protected RouteService $routeService;

    public function boot(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function showRoutesTableMessage($result)
    {
        $this->flashRoutesTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function flashRoutesTableMessage(string $text, string $type = 'success'): void
    {
        session()->flash('message', [
            'header' => 'routes-table',
            'text' => $text,
            'type' => $type,
        ]);
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

    public function render()
    {
        $carriers = collect();

        // Only admins and coordinators can see all carriers
        if (in_array(Auth::user()->role, ['admin', 'coordinator'])) {
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
