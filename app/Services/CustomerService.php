<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Note;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    public function createCustomer(array $data): array
    {
        try {
            $validated = $this->validateCustomerData($data);

            $customer = Customer::create($validated);

            if (!empty($validated['notes'])) {
                $this->createCustomerNote($customer, $validated['notes']);
            }

            return [
                'success' => true,
                'customer' => $customer,
                'message' => 'Cliente creado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ];
        }
    }

    public function updateCustomer(Customer $customer, array $data): array
    {
        try {
            $validated = $this->validateCustomerData($data, $customer->id);

            $customer->update($validated);

            return [
                'success' => true,
                'customer' => $customer->fresh(),
                'message' => 'Cliente actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }

    public function deleteCustomer(Customer $customer): array
    {
        try {
            // Check if customer has any sales (simplified check without status)
            if ($this->hasActiveSales($customer)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene ventas registradas.'
                ];
            }

            // Soft delete the customer
            $customer->delete();

            // Add a note about the deletion
            $this->createCustomerNote($customer, "Cliente eliminado el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Cliente eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ];
        }
    }

    public function forceDeleteCustomer(Customer $customer): array
    {
        try {
            // Check if customer has any sales history
            if ($customer->sales()->exists()) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar permanentemente el cliente porque tiene historial de ventas.'
                ];
            }

            // Permanently delete the customer
            $customer->forceDelete();

            return [
                'success' => true,
                'message' => 'Cliente eliminado permanentemente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar permanentemente el cliente: ' . $e->getMessage()
            ];
        }
    }

    public function searchCustomers(string $search = '', string $sortField = 'name', string $sortDirection = 'asc', int $perPage = 10, bool $includeDeleted = false)
    {
        $query = Customer::query();

        if ($includeDeleted) {
            $query->withTrashed();
        }

        return $query
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('rfc', 'like', '%' . $search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function getDeletedCustomers(string $search = '', string $sortField = 'name', string $sortDirection = 'asc', int $perPage = 10)
    {
        return Customer::onlyTrashed()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function getCustomerStats(Customer $customer): array
    {
        return [
            'total_sales' => $customer->sales()->count(),
            'total_revenue' => $customer->sales()->sum('total_amount'),
            'current_box_balance' => $customer->getBoxBalance(),
            'last_sale' => $customer->sales()->latest()->first()?->created_at,
            'registration_date' => $customer->created_at,
        ];
    }

    private function validateCustomerData(array $data, ?int $customerId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($customerId) {
            // For updates - email must be unique except for current customer
            $rules['email'][] = Rule::unique('customers')->ignore($customerId);
        } else {
            // For creation - email must be unique
            $rules['email'][] = 'unique:customers';
        }

        return validator($data, $rules)->validate();
    }

    private function createCustomerNote(Customer $customer, string $content): void
    {
        Note::create([
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => 'customer',
            'notable_id' => $customer->id,
            'notable_type' => Customer::class,
        ]);
    }

    private function hasActiveSales(Customer $customer): bool
    {
        // Simply check if customer has any sales without status filter
        // This avoids the column not found error
        return $customer->sales()->exists();

        // Alternative: Check for recent sales (last 30 days)
        // return $customer->sales()
        //     ->where('created_at', '>=', now()->subDays(30))
        //     ->exists();

        // Alternative: If you know the correct column name in your sales table, use it:
        // return $customer->sales()
        //     ->whereNotNull('some_active_field')
        //     ->exists();
    }
}
