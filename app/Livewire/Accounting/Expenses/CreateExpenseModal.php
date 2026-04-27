<?php

namespace App\Livewire\Accounting\Expenses;

use App\Models\Route;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
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
            $response = $this->expenseService->createExpense($this->getFormData());
                        
            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Gasto creado exitosamente'
                : 'Error al crear gasto');
            $type = $success ? 'success' : ($response['type'] ?? 'error');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'expenses',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('expenses-info-updated');
                $this->showCreateModal = false;
                return;
            }

            if (($type ?? 'error') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Ocurrió un error inesperado al crear el gasto: ' . $e->getMessage(),
                'type' => 'error',
                'duration' => 5000,
                'bannerId' => 'expenses',
            ]);
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
