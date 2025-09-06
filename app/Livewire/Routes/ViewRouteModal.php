<?php

namespace App\Livewire\Routes;

use App\Models\Route;
use Livewire\Component;

class ViewRouteModal extends Component
{
    public bool $showViewModal = false;

    public ?Route $selectedRoute = null;

    protected $listeners = [
        'open-view-route-modal' => 'openViewRouteModal'
    ];

    public function openViewRouteModal(int $id)
    {
        $this->selectedRoute = Route::withTrashed()->findOrFail($id);
        $this->showViewModal = true;
    }

    public function render()
    {
        return view('livewire.routes.view-route-modal');
    }
}
