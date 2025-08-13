<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    /**
     * Create a new expense with proper validation and permissions
     */
    public function createExpense(array $data): array
    {
        try {
            $validated = $this->validateExpenseData($data);

            // Set the user_id to current user if not provided
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = Auth::id();
            }

            $expense = Expense::create($validated);

            if (!empty($validated['notes'])) {
                // Handle notes if provided
                $this->createExpenseNote($expense, $validated['notes']);
            }

            return [
                'success' => true,
                'expense' => $expense,
                'message' => 'Gasto creado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear gasto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate expense data
     */
    protected function validateExpenseData(array $data): array
    {
        return validator($data, [
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ])->validate();
    }

    /**
     * Create a note for the expense
     */
    protected function createExpenseNote(Expense $expense, string $noteContent): void
    {
        Note::create([
            'notable_type' => Expense::class,
            'notable_id' => $expense->id,
            'user_id' => Auth::id(),
            'content' => $noteContent,
            'type' => 'expense',
        ]);
    }

    public function searchExpenses(
        string $search,
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 10,
        bool $includeDeletedExpenses = false,
        ?int $user_id = null,
        ?int $route_id = null
    ) {
        $query = Expense::query();

        if ($includeDeletedExpenses) {
            $query->withTrashed();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($route_id) {
            $query->where('route_id', $route_id);
        }

        return $query->with(['route', 'user'])->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }
}
