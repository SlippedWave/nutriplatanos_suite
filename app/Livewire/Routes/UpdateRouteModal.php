<?php

namespace App\Livewire\Routes;

use App\Models\Route;
use App\Models\Camera;
use App\Services\RouteService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;


class UpdateRouteModal extends Component
{
    public bool $showUpdateModal = false;

    public ?Route $route = null;

    public array $boxMovements = [];

    public $cameras = [];

    public string $title = '';

    public $user;

    protected RouteService $routeService;

    public function boot()
    {
        $this->routeService = app(RouteService::class);
    }

    #[On('open-update-route-modal')]
    public function openUpdateRouteModal()
    {
        $this->showUpdateModal = true;
    }

    public function mount(Route $route)
    {
        $this->route = $route;
        $this->user = Auth::user();
        // Load cameras for select options
        $this->cameras = Camera::select('id', 'name')->orderBy('name')->get();
        $this->boxMovements = $route->boxMovements->toArray();
        $this->title = $route->title;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'boxMovements.*.camera_id' => 'required|integer|exists:cameras,id',
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
        $valid = array_filter($this->boxMovements, function ($bm) {
            return isset($bm['camera_id'], $bm['movement_type'], $bm['quantity'], $bm['box_content_status'])
                && (int)$bm['quantity'] >= 1;
        });

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
            $this->validate();

            $data = $this->getFormData();
            // Use the service method that actually exists
            $result = $this->routeService->updateRoute($this->route, $data);

            if ($result['success']) {
                $this->showUpdateModal = false;
                $this->resetFormFields();
                session()->flash('message', $result['message'] ?? 'Ruta actualizada exitosamente.');
            } else {
                session()->flash('error', $result['message'] ?? 'Error al actualizar la ruta.');
            }
        } catch (\Illuminate\Validation\ValidationException $ve) {
            session()->flash('error', 'Datos inválidos para la ruta.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar la ruta: ' . $e->getMessage());
        }
    }



    public function render()
    {
        return view('livewire.routes.update-route-modal');
    }
}
