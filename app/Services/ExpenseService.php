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

            if (!empty($data['notes'])) {
                // Handle notes if provided
                $this->createExpenseNote($expense, $data['notes']);
            }

            return [
                'success' => true,
                'expense' => $expense,
                'message' => 'Gasto creado exitosamente.',
                'type' => 'success'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'type' => 'validation',
                'errors' => $e->errors()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear gasto: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    /**
     * Update an existing expense
     */
    public function updateExpense(int $id, array $data): array
    {
        try {
            $validated = $this->validateExpenseData($data);

            $expense = Expense::findOrFail($id);
            $expense->update($validated);

            return [
                'success' => true,
                'expense' => $expense,
                'message' => 'Gasto actualizado exitosamente.',
                'type' => 'success'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'type' => 'validation',
                'errors' => $e->errors()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear gasto: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    /**
     * Delete an existing expense
     */
    public function deleteExpense(int $id): array
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return [
                'success' => true,
                'message' => 'Gasto eliminado exitosamente.',
                'type' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar gasto: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    public function getTotalAmount(
        string $search = '',
        bool $includeDeletedExpenses = false,
        ?int $user_id = null,
        ?int $route_id = null
    ): float {
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

        return $query->sum('amount');
    }

    /**
     * Validate expense data
     */
    protected function validateExpenseData(array $data): array
    {
        return validator($data, [
            'user_id' => ['nullable', 'exists:users,id'],
            'route_id' => ['nullable', 'exists:routes,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
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
