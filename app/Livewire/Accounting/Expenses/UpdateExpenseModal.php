<?php

namespace App\Livewire\Accounting\Expenses;

use App\Models\Expense;
use App\Models\Route;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateExpenseModal extends Component
{
    public bool $showUpdateModal = false;

    public ?int $contextRouteId = null;
    public ?int $contextUserId = null;

    public ?Expense $selectedExpense = null;
    public User $currentUser;
    public $users = [];
    public $routes = [];

    public $user_id;
    public $route_id;
    public $description;
    public $amount;
    public $notes;

    protected ExpenseService $expenseService;

    public function mount(?int $contextRouteId, ?int $contextUserId)
    {
        $this->currentUser = Auth::user();
        $this->contextRouteId = $contextRouteId;
        $this->contextUserId = $contextUserId;
    }

    protected $listeners = [
        'open-update-expense-modal' => 'openUpdateExpenseModal'
    ];

    public function boot()
    {
        $this->expenseService = app(ExpenseService::class);
    }

    public function openUpdateExpenseModal(int $expenseId)
    {

        $this->selectedExpense = Expense::with('user', 'route')->findOrFail($expenseId);
        $this->user_id = $this->selectedExpense->user_id;
        $this->route_id = $this->selectedExpense->route_id;
        $this->description = $this->selectedExpense->description;
        $this->amount = $this->selectedExpense->amount;
        $this->notes = $this->selectedExpense->notes;

        $this->currentUser = Auth::user();
        $this->users = User::orderBy('name')->get();
        $this->routes = Route::with('carrier')->orderBy('title')->get(); 
        $this->showUpdateModal = true;

    }

    public function updateExpense()
    {
        try {
            $result = $this->expenseService->updateExpense($this->selectedExpense->id, $this->getFormData());
            $this->dispatch('users-info-updated');
            $this->dispatch('show-users-table-message', $result);
            $this->showUpdateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-users-table-message', $result);
        }
    }

    private function getFormData(): array
    {
        return [
            'user_id' => $this->contextUserId ?? $this->user_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'route_id' => $this->contextRouteId ?? $this->route_id,
            'notes' => $this->notes,
        ];
    }

    public function render()
    {
        return view('livewire.accounting.expenses.update-expense-modal');
    }
}
