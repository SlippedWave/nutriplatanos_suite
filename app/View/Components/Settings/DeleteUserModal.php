<?php

namespace App\View\Components\Settings;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteUserModal extends Component
{
    public $selectedUser;
    /**
     * Create a new component instance.
     */
    public function __construct($selectedUser = null)
    {
        $this->selectedUser = $selectedUser;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.settings.delete-user-modal', [
            'selectedUser' => $this->selectedUser,
        ]);
    }
}
