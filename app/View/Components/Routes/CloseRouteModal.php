<?php

namespace App\View\Components\Routes;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Services\RouteService;
use App\Models\Route;

class CloseRouteModal extends Component
{
    public $route_id;
    private RouteService $routeService;

    /**
     * Create a new component instance.
     */
    public function __construct($route_id = null)
    {
        $this->route_id = $route_id;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.routes.close-route-modal');
    }
}
