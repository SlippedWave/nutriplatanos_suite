<?php

namespace App\View\Components\Customers;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UpdateCustomerModal extends Component
{
    public $customer = null;
    /**
     * Create a new component instance.
     */
    public function __construct($customer = null) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.customers.update-customer-modal');
    }
}
