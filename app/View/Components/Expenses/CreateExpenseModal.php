<?php

namespace App\View\Components\Expenses;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CreateExpenseModal extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $routes = \App\Models\Route::all();
        $users = \App\Models\User::all();
        return view('components.expenses.create-expense-modal', [
            'routes' => $routes,
            'users' => $users,
        ]);
    }
}
