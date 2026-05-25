<?php

namespace App\Livewire\Routes;

use Livewire\Component;
use App\Services\RouteService;
use App\Models\Camera;
use App\Models\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CloseRouteModal extends Component
{
    public bool $showCloseRouteModal = false;

    public array $boxMovements = [];
    public $cameras = [];

    public $user;
    public $selectedRoute;

    protected RouteService $routeService;

    public function boot()
    {
        $this->routeService = app(RouteService::class);
    }

    #[On('open-close-route-modal')]
    public function openModal(int $id)
    {
        $this->selectedRoute = Route::findOrFail($id);
        $this->showCloseRouteModal = true;
        $this->user = Auth::user();
    }

    public function mount()
    {
        $this->cameras = Camera::select('id', 'name')->orderBy('name')->get();
    }

    protected function rules(): array
    {
        return [
            'boxMovements.*.camera_id' => 'required|integer|exists:cameras,id',
            'boxMovements.*.movement_type' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'boxMovements.*.quantity' => 'required|integer|min:1',
            'boxMovements.*.box_content_status' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
        ];
    }

    private function resetFormFields(): void
    {
        $this->boxMovements = [];
    }

    private function getFormData(): array
    {
        return [
            'boxMovements' => $this->boxMovements,
        ];
    }

    public function closeRoute()
    {
        try {
            $response = $this->routeService->closeRoute($this->selectedRoute, $this->getFormData());
    
            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success 
                ? 'Ruta cerrada exitosamente' 
                : 'Error al cerrar la ruta');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');
    
            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);
    
            if ($success) {
                $this->resetFormFields();
                $this->dispatch('routes-info-updated');
                $this->showCloseRouteModal = false;
                return;
            }
    
            // Redirect to the closed route detail
            if (!empty($response['route'])) {
                return redirect()->route('routes.show', ['route' => $response['route']->id]);
            }

        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al cerrar la ruta: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);
            return;
        }
    }

    public function render()
    {
        return view('livewire.routes.close-route-modal');
    }
}
