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
            $response = $this->expenseService->deleteExpense($this->selectedExpense->id);

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Gasto eliminado exitosamente'
                : 'Error al eliminar el gasto');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'expenses',
            ]);

            if ($success) {
                $this->showDeleteModal = false;
                $this->dispatch('expenses-info-updated');
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Ocurrió un error inesperado al eliminar el gasto: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'expenses',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.accounting.expenses.delete-expense-modal');
    }
}
