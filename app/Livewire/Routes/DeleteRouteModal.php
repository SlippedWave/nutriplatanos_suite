<?php

namespace App\Livewire\Routes;

use App\Models\Route;
use App\Services\RouteService;
use Livewire\Component;

class DeleteRouteModal extends Component
{
    public bool $showDeleteModal = false;

    public ?Route $selectedRoute = null;

    protected RouteService $routeService;

    protected $listeners = [
        'open-delete-route-modal' => 'openDeleteRouteModal'
    ];

    public function boot()
    {
        $this->routeService = app(RouteService::class);
    }

    public function openDeleteRouteModal(int $id)
    {
        $this->selectedRoute = Route::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function deleteRoute()
    {
        try {
            $result = $this->routeService->deleteRoute($this->selectedRoute);
            $this->dispatch('routes-info-updated');
            $this->dispatch('show-routes-table-message', $result);
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-routes-table-message', $result);
        }
    }

    public function render()
    {
        return view('livewire.routes.delete-route-modal');
    }
}
