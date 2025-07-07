<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Note;
use App\Models\Route;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class SaleService
{
    public function createSale(array $data): array
    {
        try {
            $validated = $this->validateSaleData($data);
            
            // Calculate total amount
            $validated['total_amount'] = $validated['weight_kg'] * $validated['price_per_kg'];
            
            // Set default payment status
            $validated['payment_status'] = $validated['payment_status'] ?? Sale::PAYMENT_STATUSES['pending'];
            
            // Set user_id to current user if not provided
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = auth()->id();
            }

            $sale = Sale::create($validated);

            if (!empty($validated['notes'])) {
                $this->createSaleNote($sale, $validated['notes']);
            }

            return [
                'success' => true,
                'sale' => $sale->load(['customer', 'route', 'user']),
                'message' => 'Venta creada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear venta: ' . $e->getMessage()
            ];
        }
    }

    public function updateSale(Sale $sale, array $data): array
    {
        try {
            // Check if sale can be updated
            if (!$this->canEditSale($sale)) {
                return [
                    'success' => false,
                    'message' => 'No se puede editar esta venta.'
                ];
            }

            $validated = $this->validateSaleData($data, $sale->id);
            
            // Recalculate total amount if weight or price changed
            if (isset($validated['weight_kg']) || isset($validated['price_per_kg'])) {
                $weight = $validated['weight_kg'] ?? $sale->weight_kg;
                $price = $validated['price_per_kg'] ?? $sale->price_per_kg;
                $validated['total_amount'] = $weight * $price;
            }

            $sale->update($validated);

            return [
                'success' => true,
                'sale' => $sale->fresh(['customer', 'route', 'user']),
                'message' => 'Venta actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar venta: ' . $e->getMessage()
            ];
        }
    }

    public function deleteSale(Sale $sale): array
    {
        try {
            if (!$this->canEditSale($sale)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar esta venta.'
                ];
            }

            // Soft delete the sale
            $sale->delete();

            $this->createSaleNote($sale, "Venta eliminada el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Venta eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar venta: ' . $e->getMessage()
            ];
        }
    }

    public function restoreSale(Sale $sale): array
    {
        try {
            $sale->restore();

            $this->createSaleNote($sale, "Venta restaurada el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Venta restaurada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al restaurar venta: ' . $e->getMessage()
            ];
        }
    }

    public function updatePaymentStatus(Sale $sale, string $paymentStatus): array
    {
        try {
            if (!in_array($paymentStatus, array_keys(Sale::PAYMENT_STATUSES))) {
                return [
                    'success' => false,
                    'message' => 'Estado de pago inválido.'
                ];
            }

            $sale->update(['payment_status' => $paymentStatus]);

            $statusLabels = [
                'pending' => 'pendiente',
                'paid' => 'pagada',
                'partial' => 'pago parcial',
                'cancelled' => 'cancelada'
            ];

            $this->createSaleNote($sale, "Estado de pago cambiado a: {$statusLabels[$paymentStatus]} el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'sale' => $sale->fresh(['customer', 'route', 'user']),
                'message' => 'Estado de pago actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar estado de pago: ' . $e->getMessage()
            ];
        }
    }

    public function searchSales(string $search = '', string $sortField = 'created_at', string $sortDirection = 'desc', int $perPage = 10, bool $includeDeleted = false, ?int $routeId = null, ?int $customerId = null, ?string $paymentStatusFilter = null)
    {
        $query = Sale::query();

        if ($includeDeleted) {
            $query->withTrashed();
        }

        // Filter by route
        if ($routeId) {
            $query->where('route_id', $routeId);
        }

        // Filter by customer
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // Filter by payment status
        if ($paymentStatusFilter) {
            $query->where('payment_status', $paymentStatusFilter);
        }

        // Filter by carrier for non-admin users
        $user = auth()->user();
        if ($user->role === 'carrier') {
            $query->whereHas('route', function ($q) use ($user) {
                $q->where('carrier_id', $user->id);
            });
        }

        return $query
            ->with(['customer', 'route', 'user'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('weight_kg', 'like', '%' . $search . '%')
                    ->orWhere('total_amount', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function getSaleStats(Sale $sale): array
    {
        return [
            'customer_name' => $sale->customer->name ?? 'Cliente eliminado',
            'route_title' => $sale->route->title ?? 'Ruta del ' . $sale->route->created_at->format('d/m/Y'),
            'user_name' => $sale->user->name ?? 'Usuario eliminado',
            'weight_kg' => $sale->weight_kg,
            'price_per_kg' => $sale->price_per_kg,
            'total_amount' => $sale->total_amount,
            'final_amount' => $sale->final_amount,
            'payment_status' => $sale->payment_status,
            'created_at' => $sale->created_at,
        ];
    }

    public function getRouteRevenue(int $routeId): array
    {
        $sales = Sale::where('route_id', $routeId)->get();
        
        return [
            'total_sales' => $sales->count(),
            'total_weight' => $sales->sum('weight_kg'),
            'total_revenue' => $sales->sum('total_amount'),
            'paid_revenue' => $sales->where('payment_status', 'paid')->sum('total_amount'),
            'pending_revenue' => $sales->where('payment_status', 'pending')->sum('total_amount'),
            'partial_revenue' => $sales->where('payment_status', 'partial')->sum('total_amount'),
        ];
    }

    public function getCustomerSales(int $customerId): array
    {
        $sales = Sale::where('customer_id', $customerId)->get();
        
        return [
            'total_purchases' => $sales->count(),
            'total_weight' => $sales->sum('weight_kg'),
            'total_spent' => $sales->sum('total_amount'),
            'pending_amount' => $sales->where('payment_status', 'pending')->sum('total_amount'),
            'last_purchase' => $sales->sortByDesc('created_at')->first()?->created_at,
        ];
    }

    private function validateSaleData(array $data, ?int $saleId = null): array
    {
        $rules = [
            'customer_id' => ['required', 'exists:customers,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'weight_kg' => ['required', 'numeric', 'min:0.001', 'max:999999.999'],
            'price_per_kg' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'payment_status' => ['nullable', 'string', Rule::in(array_keys(Sale::PAYMENT_STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        return validator($data, $rules)->validate();
    }

    private function createSaleNote(Sale $sale, string $content): void
    {
        Note::create([
            'user_id' => auth()->id(),
            'content' => $content,
            'type' => 'sale',
            'notable_id' => $sale->id,
            'notable_type' => Sale::class,
        ]);
    }

    private function canEditSale(Sale $sale): bool
    {
        $user = auth()->user();

        // Admin can edit any sale
        if ($user->role === 'admin') {
            return true;
        }

        // Carriers can only edit sales from their own active routes
        if ($user->role === 'carrier') {
            return $sale->route->carrier_id === $user->id && $sale->route->isActive();
        }

        return false;
    }
}