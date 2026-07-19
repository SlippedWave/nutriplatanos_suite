<?php

namespace App\Services;

use App\Models\Note;
use App\Models\ProductList;
use App\Models\Refund;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function createRefundNote(Refund $refund, String $noteContent): void
    {
        Note::create([
            'notable_id' => $refund->id,
            'notable_type' => Refund::class,
            'content' => $noteContent,
            'user_id' => Auth::id(),
            'type' => 'refund'
        ]);
    }

    public function createRefund(array $data): array
    {
        try {
            $validated = $this->validateRefundData($data);

            $sale = Sale::findOrFail($validated['sale_id']);
            $this->validateRefundAgainstSale($sale, $validated['refund_method'], (float) $validated['refunded_amount']);

            DB::beginTransaction();

            $refundData = collect($validated)->except('products')->toArray();
            $refund = Refund::create($refundData);

            if (!empty($validated['products'])) {
                $this->createProductList($refund, $validated['products']);
            }

            // Reflect the refund on the sale it belongs to.
            $sale->recalculateRefundTotals();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reembolso creado exitosamente.',
                'refund' => $refund,
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Hay ' . count($e->errors()) . ' error(es).',
                'validation-errors' => $e->errors(),
                'type' => 'validation-exception'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear el reembolso: ' . $e->getMessage(),
                'type' => 'exception'
            ];
        }
    }

    public function updateRefund(Refund $refund, array $data): array
    {
        try {
            $validated = $this->validateRefundData($data);

            $sale = Sale::findOrFail($validated['sale_id']);
            $this->validateRefundAgainstSale($sale, $validated['refund_method'], (float) $validated['refunded_amount']);

            DB::beginTransaction();

            $refundData = collect($validated)->except('products')->toArray();
            $refund->update($refundData);
            $this->createRefundNote($refund, "Reembolso actualizado el " . now()->format('d/m/Y H:i') . " por " . Auth::user()->name);

            $refund->productList()->delete();

            if (!empty($validated['products'])) {
                $this->createProductList($refund, $validated['products']);
            }

            // Re-sync the sale with the refund's new amount/method.
            $sale->recalculateRefundTotals();

            DB::commit();

            return [
                'success' => true,
                'refund' => $refund,
                'message' => 'Reembolso actualizado exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'validation-exception',
                'message' => 'Error de validación. Hay ' . count($e->errors()) . ' error(es).',
                'validation-errors' => $e->errors()
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'exception',
                'message' => 'Error al actualizar el reembolso: ' . $e->getMessage()
            ];
        }
    }

    public function deleteRefund(int $id): array
    {
        try {
            $refund = Refund::findOrFail($id);
            $sale = $refund->sale;

            DB::beginTransaction();
            $refund->delete();

            $this->createRefundNote($refund, 'Reembolso eliminado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            // Reverse the refund's effect on the sale now that it is gone.
            $sale?->recalculateRefundTotals();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reembolso eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar el reembolso: ' . $e->getMessage(),
                'type' => 'exception'
            ];
        }
    }

    private function validateRefundData(array $data): array
    {
        $rules = [
            'refund_method' => 'required|in:' . implode(',', array_keys(Refund::REFUND_METHODS)),
            'refunded_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'sale_id' => 'required|exists:sales,id'
        ];

        if ($data['refund_method'] === 'product') {
            $rules['products'] = 'required|array|min:1';
            $rules['products.*.product_id'] = 'required|exists:products,id';
            $rules['products.*.quantity'] = 'required|integer|min:1';
            $rules['products.*.price_per_unit'] = 'required|numeric|min:0';
        } else {
            $rules['products'] = 'nullable|array';
        }

        return validator($data, $rules)->validate();
    }

    /**
     * Enforce the business rules that tie a refund to its sale:
     *  - Discounts may only be applied while the sale still has an outstanding
     *    gross balance (i.e. pending or partial — not fully paid).
     *  - A refund of any method may not exceed the amount still pending, since
     *    there is no cash-back mechanism for the resulting overpayment.
     *
     * Both checks use the gross product value minus payments so they are stable
     * whether or not a refund already exists on the sale (create vs. update).
     */
    private function validateRefundAgainstSale(Sale $sale, string $method, float $amount): void
    {
        $gross = (float) $sale->productList()->sum('total_price');
        $paid = (float) $sale->payments()->sum('amount');
        $pending = max(0.0, $gross - $paid);

        if ($method === 'discount' && $paid >= $gross - 0.01) {
            throw ValidationException::withMessages([
                'refund_method' => 'Solo se pueden aplicar descuentos a ventas pendientes o con pago parcial.',
            ]);
        }

        if ($amount > $pending + 0.01) {
            throw ValidationException::withMessages([
                'refunded_amount' => 'El monto del reembolso no puede exceder el monto pendiente de $' . number_format($pending, 2) . '.',
            ]);
        }
    }

    private function createProductList(Refund $refund, array $products): void
    {
        foreach ($products as $productData) {
            ProductList::create([
                'listable_type' => Refund::class,
                'listable_id' => $refund->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'price_per_unit' => $productData['price_per_unit']
            ]);
        }
    }

}
