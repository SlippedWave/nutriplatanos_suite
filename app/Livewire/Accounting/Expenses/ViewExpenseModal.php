<?php

namespace App\Livewire\Accounting\Expenses;

use App\Models\Expense;
use Livewire\Component;

class ViewExpenseModal extends Component
{
    public bool $showViewModal = false;

    public ?Expense $selectedExpense = null;

    protected $listeners = [
        'open-view-expense-modal' => 'openViewExpenseModal'
    ];

    public function openViewExpenseModal(int $expenseId)
    {
        $this->selectedExpense = Expense::with('user', 'route')->findOrFail($expenseId);
        $this->showViewModal = true;
    }

    public function render()
    {
        return view('livewire.accounting.expenses.view-expense-modal');
    }
}
