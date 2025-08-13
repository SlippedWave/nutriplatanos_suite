<?php

namespace App\Livewire\Expenses\Tables;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

use App\Services\ExpenseService;

class ExpensesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $includeDeleted = false;

    public bool $showCreateExpenseModal = false;
    public bool $showEditExpenseModal = false;
    public bool $showDeleteExpenseModal = false;
    public bool $showViewExpenseModal = false;

    //Form fields
    public $user_id;
    public $description;
    public $amount;
    public $route_id;
    public $notes;

    public bool $canCreateExpense = false;

    public $dateFilter = 'all';
    public $startDate;
    public $endDate;

    public $contextRouteId = null;
    public $contextUserId = null;

    public ?Expense $selectedExpense = null;

    protected ExpenseService $expenseService;

    protected function showExpensesTableMessage($result)
    {
        $this->closeModals();
        $this->flashExpensesTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function mount($route_id = null, $user_id = null)
    {
        $this->contextRouteId = $route_id;
        $this->contextUserId = $user_id;

        $this->expenseService = new ExpenseService();

        if ($route_id) {
            $this->canCreateExpense = auth()->user()->can('create', Expense::class);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->applyDateFilter();
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
        $this->resetPage();
    }

    private function applyDateFilter()
    {
        $now = now();

        switch ($this->dateFilter) {
            case 'today':
                $this->startDate = $now->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                break;
            case 'week':
                $this->startDate = $now->startOfWeek()->toDateString();
                $this->endDate = $now->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->toDateString();
                $this->endDate = $now->endOfMonth()->toDateString();
                break;
            default:
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreateExpenseModal()
    {
        $this->resetFormFields();
        $this->resetValidation();
        session()->forget('message');
        $this->showCreateExpenseModal = true;
    }

    public function openEditExpenseModal($saleId)
    {
        $this->selectedExpense = Expense::with('user', 'description', 'amount', 'route')->find($saleId);
        $this->fillForm();
        $this->resetValidation();
        session()->forget('message');
        $this->showEditExpenseModal = true;
    }

    public function openDeleteExpenseModal($saleId)
    {
        $this->selectedExpense = Expense::findOrFail($saleId);
        $this->resetValidation();
        session()->forget('message');
        $this->showDeleteExpenseModal = true;
    }

    private function resetFormFields()
    {
        $this->user_id = $this->contextUserId ?? null;
        $this->description = '';
        $this->amount = null;
        $this->route_id = $this->contextRouteId ?? null;
        $this->notes = '';
    }

    private function fillForm()
    {
        $this->user_id = $this->selectedExpense->user_id;
        $this->description = $this->selectedExpense->description;
        $this->amount = $this->selectedExpense->amount;
        $this->route_id = $this->selectedExpense->route_id;
        $this->notes = '';
    }

    public function closeModals()
    {
        $this->showCreateExpenseModal = false;
        $this->showEditExpenseModal = false;
        $this->showDeleteExpenseModal = false;
        $this->showViewExpenseModal = false;
        $this->resetFormFields();
        $this->resetValidation();
        $this->selectedExpense = null;

        session()->forget('message');
    }



    protected function flashExpensesTableMessage(string $message, string $type)
    {
        session()->flash('message', [
            'header' => 'expenses-table',
            'body' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        return view('livewire.expenses.expenses-table');
    }
}
