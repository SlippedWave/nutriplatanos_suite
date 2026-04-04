<?php

namespace App\Livewire\Accounting\Expenses;

use App\Models\Route;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateExpenseModal extends Component
{
    public bool $showCreateModal = false;

    public ?int $contextRouteId = null;
    public ?int $contextUserId = null;
    public User $currentUser;
    public $users = [];
    public $routes = [];

    public $user_id = null;
    public $route_id = null;
    public $description = '';
    public $amount = 0;
    public $notes = '';

    protected ExpenseService $expenseService;

    protected $listeners = [
        'open-create-expense-modal' => 'openCreateExpenseModal'
    ];

    public function boot()
    {
        $this->expenseService = app(ExpenseService::class);
    }

    public function mount(?int $contextRouteId, ?int $contextUserId)
    {
        $this->contextRouteId = $contextRouteId;
        $this->contextUserId = $contextUserId;
        $this->currentUser = Auth::user();
        $this->users = User::orderBy('name')->get();
        $this->routes = Route::with('carrier')->orderBy('title')->get();
    }

    public function openCreateExpenseModal()
    {
        $this->resetValidation();
        $this->user_id = $this->contextUserId ?? Auth::user()->id;
        $this->route_id = $this->contextRouteId ?? null;
        $this->reset(['description', 'amount', 'notes']);
        session()->forget('message');
        $this->showCreateModal = true;
    }

    public function createExpense()
    {
        try {
            $result = $this->expenseService->createExpense($this->getFormData());
            $this->dispatch('expenses-info-updated');
            $this->dispatch('show-expenses-table-message', $result);
            $this->showCreateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('show-expenses-table-message', $result);
        }
    }

    private function getFormData(): array
    {
        return [
            'user_id' => $this->user_id ?? $this->contextUserId,
            'description' => $this->description,
            'amount' => $this->amount,
            'route_id' => $this->contextRouteId ?? $this->route_id,
            'notes' => $this->notes,
        ];
    }

    public function render()
    {
        return view('livewire.accounting.expenses.create-expense-modal');
    }
}
