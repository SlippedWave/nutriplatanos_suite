<?php

namespace App\Livewire\Expenses\Tables;

use App\Models\Expense;
use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;

class ExpensesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $includeDeletedExpenses = false;

    public bool $showCreateExpenseModal = false;
    public bool $showEditExpenseModal = false;
    public bool $showDeleteExpenseModal = false;
    public bool $showViewExpenseModal = false;

    protected $rules = [
        'user_id' => 'nullable|exists:users,id',
        'route_id' => 'nullable|exists:routes,id',
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ];

    //Form fields
    public $user_id;
    public $route_id;
    public $description;
    public $amount;
    public $notes;

    public bool $canCreateNewExpense = false;

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

    public function boot(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function mount($route_id = null, $user_id = null)
    {
        $this->contextRouteId = $route_id;
        $this->contextUserId = $user_id;

        $this->expenseService = new ExpenseService();

        $this->canCreateNewExpense = !empty($this->contextRouteId)
            && Route::where('id', $this->contextRouteId)->where('status', 'active')->exists()
            && (Auth::user()->role === 'admin' || Route::where('id', $this->contextRouteId)->where('user_id', Auth::id())->exists());
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

    public function openCreateExpenseModal()
    {
        $this->resetFormFields();
        $this->resetValidation();
        session()->forget('message');
        $this->showCreateExpenseModal = true;
    }

    public function openEditExpenseModal($saleId)
    {
        $this->selectedExpense = Expense::with('user', 'route')->find($saleId);
        $this->fillForm();
        $this->resetValidation();
        session()->forget('message');
        $this->showEditExpenseModal = true;
    }

    public function openViewExpenseModal($saleId)
    {
        $this->selectedExpense = Expense::with('user', 'route')->find($saleId);
        $this->resetValidation();
        session()->forget('message');
        $this->showViewExpenseModal = true;
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

    public function createExpense()
    {
        // Validate on the Livewire side first so errors render in the modal
        $this->resetValidation();
        $this->validate($this->rules);

        try {
            $result = $this->expenseService->createExpense($this->getFormData());

            if ($result['success']) {
                $this->showExpensesTableMessage($result);
            } else {
                switch ($result['type'] ?? 'error') {
                    case 'validation':
                        // Populate any extra backend validation errors into the modal
                        if (isset($result['errors'])) {
                            foreach ($result['errors'] as $field => $messages) {
                                $this->addError($field, implode(' ', $messages));
                            }
                        }
                        $this->flashExpensesTableMessage($result['message'], 'error');
                        break;
                    default:
                        $this->showExpensesTableMessage($result);
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->flashExpensesTableMessage('Error al crear el gasto: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function updateExpense()
    {
        $this->resetValidation();
        $this->validate($this->rules);

        try {
            $result = $this->expenseService->updateExpense($this->selectedExpense->id, $this->getFormData());

            if ($result['success']) {
                $this->showExpensesTableMessage($result);
            } else {
                switch ($result['type'] ?? 'error') {
                    case 'validation':
                        if (isset($result['errors'])) {
                            foreach ($result['errors'] as $field => $messages) {
                                $this->addError($field, implode(' ', $messages));
                            }
                        }
                        $this->flashExpensesTableMessage($result['message'], 'error');
                        break;
                    default:
                        $this->showExpensesTableMessage($result);
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->flashExpensesTableMessage('Error al editar el gasto: ' . $e->getMessage(), 'error');
            return;
        }
    }

    public function deleteExpense()
    {
        try {
            $result = $this->expenseService->deleteExpense($this->selectedExpense->id);

            if ($result['success']) {
                $this->showExpensesTableMessage($result);
            } else {
                $this->flashExpensesTableMessage($result['message'], 'error');
            }
        } catch (\Exception $e) {
            $this->flashExpensesTableMessage('Error al eliminar el gasto: ' . $e->getMessage(), 'error');
        }
    }

    private function getFormData(): array
    {
        return [
            'user_id' => $this->user_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'route_id' => $this->route_id,
            'notes' => $this->notes,
        ];
    }

    protected function flashExpensesTableMessage(string $message, string $type)
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
            route_id: $this->contextRouteId
        );

        // Calculate total amount from non-deleted expenses using a separate query
        $totalAmount = $this->expenseService->getTotalAmount(
            search: $this->search,
            includeDeletedExpenses: false,
            user_id: $this->contextUserId,
            route_id: $this->contextRouteId
        );

        return view('livewire.expenses.expenses-table', compact('expenses', 'totalAmount'));
    }
}
