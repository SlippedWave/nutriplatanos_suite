<?php

namespace App\View\Components\Routes;

use Livewire\Component;

class CreateRouteModal extends Component
{
    public $showCreateModal = false;
    public $boxMovements = [];
    public $title = '';
    public $notes = '';

    protected $listeners = ['openCreateRouteModal'];

    public function mount()
    {
        $this->resetForm();
    }

    public function openCreateRouteModal()
    {
        $this->showCreateModal = true;
        $this->resetForm();
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->title = 'Ruta del día ' . now()->format('d/m/Y');
        $this->notes = '';
        $this->boxMovements = [
            [
                'quantity' => '',
                'movement_type' => 'warehouse_to_route',
                'box_content_status' => '',
                'notes' => ''
            ]
        ];
    }

    public function addBoxMovement()
    {
        $this->boxMovements[] = [
            'quantity' => '',
            'movement_type' => 'warehouse_to_route',
            'box_content_status' => '',
            'notes' => ''
        ];
    }

    public function removeBoxMovement($index)
    {
        if (count($this->boxMovements) > 1) {
            unset($this->boxMovements[$index]);
            $this->boxMovements = array_values($this->boxMovements);
        }
    }

    public function createRoute()
    {
        $this->validate($this->getValidationRules());

        try {
            // Create route logic here
            // You'll need to implement the route creation service call

            $this->closeModals();
            $this->dispatch('route-created');
            session()->flash('message', 'Ruta creada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la ruta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('components.routes.create-route-modal');
    }

    private function getValidationRules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($this->boxMovements as $index => $movement) {
            $rules["boxMovements.{$index}.quantity"] = ['required', 'integer', 'min:0'];
            $rules["boxMovements.{$index}.movement_type"] = ['required', 'string', 'in:warehouse_to_route,route_to_warehouse,route_to_route,truck_inventory'];
            $rules["boxMovements.{$index}.box_content_status"] = ['required', 'string', 'in:empty,full'];
            $rules["boxMovements.{$index}.notes"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }
}
