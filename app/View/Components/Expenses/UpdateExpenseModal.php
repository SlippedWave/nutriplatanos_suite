<?php

namespace App\View\Components\Expenses;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateExpenseModal extends Component
{
    public $routes;
    public $users;
    public ?User $currentUser;

    public ?int $contextUserId;
    public ?int $contextRouteId;

    /**
     * Create a new component instance.
     */
    public function __construct(?int $contextUserId = null, ?int $contextRouteId = null)
    {
        $this->contextUserId = $contextUserId;
        $this->contextRouteId = $contextRouteId;

        $this->routes = \App\Models\Route::withoutTrashed()->where('status', 'active')->get();
        $this->users = \App\Models\User::withoutTrashed()->where('active', true)->get();
        $this->currentUser = Auth::user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.expenses.update-expense-modal');
    }
}
