<?php

namespace App\View\Components\Routes;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CloseRouteModal extends Component
{
    public bool $route_id;
    /**
     * Create a new component instance.
     */
    public function __construct($route_id = null)
    {
        $this->route_id = $route_id;
    }

    /**
     * Close the route
     */
    public function closeRoute()
    {
        if ($this->route_id) {
            // Logic to close the route
            // This could involve updating the route status, logging the closure, etc.
            // For example:
            $route = \App\Models\Route::find($this->route_id);
            if ($route) {
                $route->status = 'closed'; // Assuming 'closed' is a valid status
                $route->save();
                session()->flash('message', __('Ruta cerrada exitosamente.'));
            } else {
                session()->flash('error', __('Ruta no encontrada.'));
            }
        } else {
            session()->flash('error', __('ID de ruta no proporcionado.'));
        }
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.routes.close-route-modal');
    }
}
