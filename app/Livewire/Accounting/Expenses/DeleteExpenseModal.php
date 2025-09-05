<?php

namespace App\Livewire\Accounting\Expenses;

use App\Models\Expense;
use App\Services\ExpenseService;
use Livewire\Component;

class DeleteExpenseModal extends Component
{
    public bool $showDeleteModal = false;

    public ?Expense $selectedExpense = null;

    protected ExpenseService $expenseService;

    protected $listeners = [
        'open-delete-expense-modal' => 'openDeleteExpenseModal'
    ];

    public function boot()
    {
        $this->expenseService = app(ExpenseService::class);
    }

    public function openDeleteExpenseModal(int $expenseId)
    {
        $this->selectedExpense = Expense::with('user', 'route')->findOrFail($expenseId);
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        try {
            $result = $this->expenseService->deleteExpense($this->selectedExpense->id);
            $this->dispatch('users-info-updated');
            $this->dispatch('show-users-table-message', $result);
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-users-table-message', $result);
        }
    }

    public function render()
    {
        return view('livewire.accounting.expenses.delete-expense-modal');
    }
}
