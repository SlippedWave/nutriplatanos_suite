<?php

namespace App\Livewire\Routes;

use Livewire\Component;
use Livewire\Attributes\Modelable;

class BoxMovementsEditor extends Component
{
    #[Modelable]
    public array $model = []; // will be bound from parent via wire:model

    public $cameras = [];
    public bool $editable = true;

    public function mount($cameras = [], bool $editable = true): void
    {
        $this->cameras = $cameras;
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
            'movement_type' => array_key_first(\App\Models\BoxMovement::MOVEMENT_TYPES) ?? 'warehouse_to_route',
            'quantity' => 1,
            'box_content_status' => array_key_first(\App\Models\BoxMovement::BOX_CONTENT_STATUSES) ?? 'full',
        ];
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
