<?php

namespace App\Livewire\Routes;

use Livewire\Component;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;

class BoxMovementsEditor extends Component
{
    #[Modelable]
    public array $model = []; // will be bound from parent via wire:model

    public $cameras = [];
    public $routes = [];

    // Reactive so it updates when the parent modal opens for a specific route.
    #[Reactive]
    public ?int $currentRouteId = null;

    public bool $editable = true;

    public function mount($cameras = [], $routes = [], ?int $currentRouteId = null, bool $editable = true): void
    {
        $this->cameras = $cameras;
        $this->routes = $routes;
        $this->currentRouteId = $currentRouteId;
        $this->editable = $editable;
    }

    public function addMovement(): void
    {
        if (! $this->editable) return;

        $defaultCameraId = null;
        if ($this->cameras instanceof \Illuminate\Support\Collection) {
            $defaultCameraId = optional($this->cameras->first())->id;
        } elseif (is_array($this->cameras) && !empty($this->cameras)) {
            $first = $this->cameras[0];
            $defaultCameraId = is_array($first) ? ($first['id'] ?? null) : ($first->id ?? null);
        }

        $this->model[] = [
            'camera_id' => $defaultCameraId,
            'related_route_id' => null,
            'transfer_direction' => array_key_first(\App\Models\BoxMovement::TRANSFER_DIRECTIONS) ?? 'out',
            'movement_type' => array_key_first(\App\Models\BoxMovement::MOVEMENT_TYPES) ?? 'warehouse_to_route',
            'quantity' => 1,
            'box_content_status' => array_key_first(\App\Models\BoxMovement::BOX_CONTENT_STATUSES) ?? 'full',
        ];
    }

    public function updatedModel(mixed $value, ?string $key = null): void
    {
        if ($key === null) return;

        // When movement_type changes, reset fields that don't apply to the new type.
        if (str_ends_with($key, '.movement_type')) {
            $index = (int) explode('.', $key)[0];

            if (!in_array($value, ['warehouse_to_route', 'route_to_warehouse'])) {
                $this->model[$index]['camera_id'] = null;
            }

            if ($value === 'route_to_route') {
                $this->model[$index]['transfer_direction'] = $this->model[$index]['transfer_direction']
                    ?? (array_key_first(\App\Models\BoxMovement::TRANSFER_DIRECTIONS) ?? 'out');
                $this->model[$index]['related_route_id'] = $this->model[$index]['related_route_id']
                    ?? $this->firstCounterpartRouteId();
            } else {
                $this->model[$index]['related_route_id'] = null;
                $this->model[$index]['transfer_direction'] = null;
            }
        }
    }

    /**
     * First route usable as a counterpart (excluding the route being edited).
     */
    private function firstCounterpartRouteId(): ?int
    {
        foreach ($this->routes as $route) {
            $id = is_array($route) ? ($route['id'] ?? null) : ($route->id ?? null);
            if ($id !== null && (int) $id !== (int) $this->currentRouteId) {
                return (int) $id;
            }
        }
        return null;
    }

    public function removeMovement(int $index): void
    {
        if (! $this->editable) return;

        if (isset($this->model[$index])) {
            unset($this->model[$index]);
            $this->model = array_values($this->model);
        }
    }

    public function render()
    {
        return view('livewire.routes.box-movements-editor');
    }
}
