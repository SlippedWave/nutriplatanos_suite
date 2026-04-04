<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class WelcomeSection extends Component
{
    public string $userName;
    public string $welcomeMessage;
    /**
     * Create a new component instance.
     */
    public function __construct($welcomeMessage)
    {
        $this->userName = Auth::check() ? Auth::user()->name : 'Usuario';
        $this->welcomeMessage = $welcomeMessage;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.welcome-section');
    }
}
