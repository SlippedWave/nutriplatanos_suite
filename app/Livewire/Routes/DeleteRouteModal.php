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
            $response = $this->routeService->deleteRoute($this->selectedRoute);

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Ruta eliminada exitosamente'
                : 'Error al eliminar ruta');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);

            if ($success) {
                $this->dispatch('routes-info-updated');
                $this->showDeleteModal = false;
                return;
            }
            
            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al eliminar la ruta: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);
            return;
        }
    }

    public function render()
    {
        return view('livewire.routes.delete-route-modal');
    }
}
