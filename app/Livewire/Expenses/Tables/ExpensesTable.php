<?php

namespace App\Livewire\Expenses\Tables;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

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

    public bool $canCreateExpense = false;

    public $dateFilter = 'all';
    public $startDate;
    public $endDate;

    public $contextRouteId = null;
    public $contextUserId = null;

    public ?Customer $selectedCustomer = null;

    protected ExpenseService $expenseService;




    public function render()
    {
        return view('livewire.expenses.expenses-table');
    }
}
