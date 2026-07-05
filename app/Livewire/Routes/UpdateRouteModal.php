<?php

namespace App\Livewire\Routes;

use App\Models\Route;
use App\Models\Camera;
use App\Services\RouteService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Livewire\Attributes\On;


class UpdateRouteModal extends Component
{
    public bool $showUpdateModal = false;

    public ?Route $route = null;

    public array $boxMovements = [];

    public $cameras = [];
    public array $routes = [];

    public string $title = '';

    public $user;

    public $carriers = [];

    protected RouteService $routeService;

    public function boot()
    {
        $this->routeService = app(RouteService::class);
        $this->carriers = \App\Models\User::where('role', 'carrier')->select('id', 'name')->orderBy('name')->get();
    }

    #[On('open-update-route-modal')]
    public function openForRoute(int $id): void
    {
        $route = Route::with('boxMovements')->findOrFail($id);
        $this->fillFromRoute($route);
        $this->showUpdateModal = true;
    }

    public function fillFromRoute(Route $route): void
    {
        $this->route = $route;
        $this->user = Auth::user();
        $this->boxMovements = $route->boxMovements->toArray();
        $this->title = $route->title;
    }

    public function mount()
    {
        $this->cameras = Camera::select('id', 'name')->orderBy('name')->get();
        $this->routes = Route::routeTransferOptions();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'boxMovements.*.camera_id' => 'nullable|integer|exists:cameras,id',
            'boxMovements.*.related_route_id' => 'nullable|integer|exists:routes,id',
            'boxMovements.*.transfer_direction' => 'nullable|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::TRANSFER_DIRECTIONS)),
            'boxMovements.*.movement_type' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'boxMovements.*.quantity' => 'required|integer|min:1',
            'boxMovements.*.box_content_status' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
        ];
    }

    public function addBoxMovement(): void
    {
        $defaultCameraId = null;
        if ($this->cameras instanceof \Illuminate\Support\Collection) {
            $defaultCameraId = optional($this->cameras->first())->id;
        } elseif (is_array($this->cameras) && !empty($this->cameras)) {
            $first = $this->cameras[0];
            $defaultCameraId = is_array($first) ? ($first['id'] ?? null) : ($first->id ?? null);
        }

        $this->boxMovements[] = [
            'camera_id' => $defaultCameraId,
            'related_route_id' => null,
            'transfer_direction' => array_key_first(\App\Models\BoxMovement::TRANSFER_DIRECTIONS) ?? 'out',
            'movement_type' => array_key_first(\App\Models\BoxMovement::MOVEMENT_TYPES) ?? 'warehouse_to_route',
            'quantity' => 1,
            'box_content_status' => array_key_first(\App\Models\BoxMovement::BOX_CONTENT_STATUSES) ?? 'full',
        ];
    }

    public function removeBoxMovement(int $index): void
    {
        if (count($this->boxMovements) >= 1) {
            unset($this->boxMovements[$index]);
            $this->boxMovements = array_values($this->boxMovements);
        }
    }

    public function resetFormFields(): void
    {
        if ($this->route) {
            $this->title = $this->route->title;
            $this->boxMovements = $this->route->boxMovements->toArray();
        }
    }

    private function getFormData(): array
    {
        $valid = array_filter($this->boxMovements, fn ($bm) => Route::isCompleteBoxMovementRow($bm));

        return [
            'title' => $this->title,
            'boxMovements' => array_values($valid),
            'carrier_id' => $this->user->id,
            'status' => 'active',
        ];
    }

    public function updateRoute()
    {
        try {
            $data = $this->getFormData();

            $response = $this->routeService->updateRoute($this->route, $data);

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Ruta actualizada exitosamente'
                : 'Error al actualizar la ruta');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('routes-info-updated');
                $this->showUpdateModal = false;
                return;
            }

            if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }

            return;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar la ruta: ' . $e->getMessage());
        }
    }



    public function render()
    {
        return view('livewire.routes.update-route-modal');
    }
}
