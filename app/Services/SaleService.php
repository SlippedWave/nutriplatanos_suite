<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Note;
use App\Models\Route;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleService
{
    public function createSale(array $data): array
    {
        try {
            $validated = $this->validateSaleData($data);

            DB::beginTransaction();

            // Set user_id to current user if not provided
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = Auth::id();
            }

            // Create the sale without products data
            $saleData = collect($validated)->except(['products', 'notes'])->toArray();
            $sale = Sale::create($saleData);

            // Create sale details if products are provided
            if (!empty($validated['products'])) {
                $this->createSaleDetails($sale, $validated['products']);
            }

            // Create note if provided
            if (!empty($validated['notes'])) {
                $this->createSaleNote($sale, $validated['notes']);
            }

            DB::commit();

            return [
                'success' => true,
                'sale' => $sale->load(['customer', 'route', 'user', 'saleDetails.product']),
                'message' => 'Venta creada exitosamente.',
                'type' => 'success'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Por favor, revisa los datos ingresados.',
                'errors' => $e->errors(),
                'type' => 'validation'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear venta: ' . $e->getMessage(),
                'type' => 'error'
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
                    'message' => 'No se puede editar esta venta.',
                    'type' => 'authorization'
                ];
            }

            $validated = $this->validateSaleData($data, $sale->id);

            DB::beginTransaction();

            // Update the sale without products data
            $saleData = collect($validated)->except(['products', 'notes'])->toArray();
            $sale->update($saleData);

            // Update sale details if products are provided
            if (isset($validated['products'])) {
                // Delete existing sale details
                $sale->saleDetails()->delete();

                // Create new sale details
                if (!empty($validated['products'])) {
                    $this->createSaleDetails($sale, $validated['products']);
                }
            }

            // Create note if provided
            if (!empty($validated['notes'])) {
                $this->createSaleNote($sale, $validated['notes']);
            }

            DB::commit();

            return [
                'success' => true,
                'sale' => $sale->fresh(['customer', 'route', 'user', 'saleDetails.product']),
                'message' => 'Venta actualizada exitosamente.',
                'type' => 'success'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Por favor, revisa los datos ingresados.',
                'errors' => $e->errors(),
                'type' => 'validation'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar venta: ' . $e->getMessage(),
                'type' => 'error'
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

    public function searchSales(
        string $search = '',
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 10,
        bool $includeDeleted = false,
        ?int $routeId = null,
        ?int $customerId = null,
        ?bool $showPendingAndPartialSales = false
    ) {
        $query = Sale::query();

        if ($includeDeleted) {
            $query->withTrashed();
        }

        if ($routeId && !$showPendingAndPartialSales) {
            $query->where('route_id', $routeId);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $user = Auth::user();
        if ($user->role === 'carrier' && !$showPendingAndPartialSales) {
            $query->whereHas('route', function ($q) use ($user) {
                $q->where('carrier_id', $user->id);
            });
        }

        if ($showPendingAndPartialSales || !$customerId) {
            $query->whereIn('payment_status', ['pending', 'partial']);
        }

        $query = $query
            ->with(['customer', 'route', 'user', 'saleDetails.product'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('saleDetails.product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhere('payment_status', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);

        return $query;
    }

    public function getSaleStats(Sale $sale): array
    {
        $sale->load(['saleDetails.product', 'customer', 'route', 'user']);

        $totalAmount = $sale->saleDetails->sum('total_price');
        $totalItems = $sale->saleDetails->count();
        $totalQuantity = $sale->saleDetails->sum('quantity');

        return [
            'customer_name' => $sale->customer->name ?? 'Cliente eliminado',
            'route_title' => $sale->route->title ?? 'Ruta del ' . $sale->route->created_at->format('d/m/Y'),
            'user_name' => $sale->user->name ?? 'Usuario eliminado',
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'payment_status' => $sale->payment_status,
            'created_at' => $sale->created_at,
            'products' => $sale->saleDetails->map(function ($detail) {
                return [
                    'name' => $detail->product->name ?? 'Producto eliminado',
                    'quantity' => $detail->quantity,
                    'price_per_unit' => $detail->price_per_unit,
                    'total_price' => $detail->total_price,
                ];
            }),
        ];
    }

    public function getRouteRevenue(int $routeId): array
    {
        $sales = Sale::with('saleDetails')
            ->where('route_id', $routeId)
            ->get();

        $totalRevenue = $sales->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        $paidRevenue = $sales->where('payment_status', 'paid')->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        $pendingRevenue = $sales->where('payment_status', 'pending')->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        $partialRevenue = $sales->where('payment_status', 'partial')->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        return [
            'total_sales' => $sales->count(),
            'total_items' => $sales->sum(function ($sale) {
                return $sale->saleDetails->sum('quantity');
            }),
            'total_revenue' => $totalRevenue,
            'paid_revenue' => $paidRevenue,
            'pending_revenue' => $pendingRevenue,
            'partial_revenue' => $partialRevenue,
        ];
    }

    public function getCustomerSales(int $customerId): array
    {
        $sales = Sale::with('saleDetails')
            ->where('customer_id', $customerId)
            ->get();

        $totalSpent = $sales->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        $pendingAmount = $sales->where('payment_status', 'pending')->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        return [
            'total_purchases' => $sales->count(),
            'total_items' => $sales->sum(function ($sale) {
                return $sale->saleDetails->sum('quantity');
            }),
            'total_spent' => $totalSpent,
            'pending_amount' => $pendingAmount,
            'last_purchase' => $sales->sortByDesc('created_at')->first()?->created_at,
        ];
    }

    private function validateSaleData(array $data, ?int $saleId = null): array
    {
        $rules = [
            'customer_id' => ['required', 'exists:customers,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'payment_status' => ['nullable', 'string', Rule::in(array_keys(Sale::PAYMENT_STATUSES))],
            'total_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999.999'],
            'products.*.price_per_unit' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];

        return validator($data, $rules)->validate();
    }

    private function createSaleNote(Sale $sale, string $content): void
    {
        Note::create([
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => 'sale',
            'notable_id' => $sale->id,
            'notable_type' => Sale::class,
        ]);
    }

    private function createSaleDetails(Sale $sale, array $products): void
    {
        foreach ($products as $productData) {
            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'price_per_unit' => $productData['price_per_unit'],
            ]);
        }
    }

    private function canEditSale(Sale $sale): bool
    {
        $user = Auth::user();

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
