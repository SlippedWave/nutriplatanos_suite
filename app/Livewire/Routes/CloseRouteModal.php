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
        $this->validate();

        $result = $this->routeService->closeRoute($this->selectedRoute, $this->getFormData());

        if (!($result['success'] ?? false)) {
            // Map service validation errors back to Livewire error bag
            if (!empty($result['errors']) && is_array($result['errors'])) {
                foreach ($result['errors'] as $field => $messages) {
                    // Prefix nested boxMovements errors to match Livewire properties if needed
                    $this->addError($field, is_array($messages) ? implode("\n", $messages) : (string) $messages);
                }
            }
            session()->flash('error', $result['message'] ?? 'No se pudo cerrar la ruta.');
            // Notify parent so it can show a toast/banner
            $this->dispatch('route-close-failed', message: $result['message'] ?? 'Fallo al cerrar la ruta', errors: $result['errors'] ?? null);
            return;
        }

        $this->resetFormFields();
        $this->showCloseRouteModal = false;

        session()->flash('message', 'Ruta cerrada exitosamente!');
        $this->dispatch('route-closed');

        // Redirect to the closed route detail
        if (!empty($result['route'])) {
            return redirect()->route('routes.show', ['route' => $result['route']->id]);
        }
    }

    public function render()
    {
        return view('livewire.routes.close-route-modal');
    }
}
