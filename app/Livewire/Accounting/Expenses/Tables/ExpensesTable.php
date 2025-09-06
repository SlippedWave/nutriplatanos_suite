<?php

namespace App\Livewire\Accounting\Expenses\Tables;

use App\Models\Expense;
use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Modelable;

class ExpensesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 5;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $includeDeletedExpenses = false;
    public bool $hideFilters = false;

    public bool $canCreateNewExpense = false;

    
    #[Modelable]
    public $dateFilter = 'all';
    public $startDate;
    public $endDate;
    
    public $contextRouteId = null;
    public $contextUserId = null;
    protected $listeners = [
        'expenses-info-updated' => '$refresh',
        'show-expenses-table-message' => 'showExpensesTableMessage',
        'flash-expenses-table-message' => 'flashExpensesTableMessage',
    ];
    
    public ?Expense $selectedExpense = null;

    protected ExpenseService $expenseService;

    public function showExpensesTableMessage($result)
    {
        $this->flashExpensesTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function boot(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function mount($route_id = null, $user_id = null, $hideFilters = false, $dateFilter = 'all')
    {
        $this->contextRouteId = $route_id;
        $this->contextUserId = $user_id;
        $this->hideFilters = $hideFilters;
        $this->dateFilter = $dateFilter;

        $this->expenseService = new ExpenseService();

        $this->canCreateNewExpense = !empty($this->contextRouteId)
            && Route::where('id', $this->contextRouteId)->where('status', 'active')->exists()
            && (Auth::user()->role === 'admin' || Route::where('id', $this->contextRouteId)->where('user_id', Auth::id())->exists())
            || empty($this->contextRouteId) && empty($this->contextUserId);

        // Initialize date filter window
        $this->applyDateFilter();
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

    public function toggleIncludeDeletedExpenses()
    {
        $this->includeDeletedExpenses = !$this->includeDeletedExpenses;
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

    public function flashExpensesTableMessage(string $message, string $type)
    {
        session()->flash('message', [
            'header' => 'expenses-table',
            'text' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        $expenses = $this->expenseService->searchExpenses(
            search: $this->search,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage,
            includeDeletedExpenses: $this->includeDeletedExpenses,
            user_id: $this->contextUserId,
            route_id: $this->contextRouteId,
            startDate: $this->startDate,
            endDate: $this->endDate,
        );

        // Calculate total amount from non-deleted expenses using a separate query
        $totalAmount = $this->expenseService->getTotalAmount(
            search: $this->search,
            includeDeletedExpenses: false,
            user_id: $this->contextUserId,
            route_id: $this->contextRouteId,
            startDate: $this->startDate,
            endDate: $this->endDate,
        );
        $this->dispatch('expensesTotalUpdated', $totalAmount);

        return view('livewire.accounting.expenses.tables.expenses-table', compact('expenses', 'totalAmount'));
    }
}
