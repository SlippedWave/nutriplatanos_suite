<?php

namespace App\Services;

use App\Models\Note;
use App\Models\ProductList;
use App\Models\Refund;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            DB::beginTransaction();

            $refundData = collect($validated)->except('products')->toArray();
            $refund = Refund::create($refundData);

            if (!empty($validated['products'])) {
                $this->createProductList($refund, $validated['products']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reembolso creado exitosamente.',
                'refund' => $refund,
                'type' => 'success'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Por favor, revisa los datos ingresados.' . $e->getMessage(),
                'errors' => $e->errors(),
                'type' => 'validation'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear el reembolso: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    public function updateRefund(Refund $refund, array $data): array
    {
        try {
            $validated = $this->validateRefundData($data);

            DB::beginTransaction();

            $refundData = collect($validated)->except('products')->toArray();
            $refund->update($refundData);
            $this->createRefundNote($refund, "Reembolso actualizado el " . now()->format('d/m/Y H:i') . " por " . Auth::user()->name);

            $refund->productLists()->delete();
            if (!empty($validated['products'])) {
                $this->createProductList($refund, $validated['products']);
            }

            DB::commit();

            return [
                'success' => true,
                'refund' => $refund,
                'message' => 'Reembolso actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el reembolso: ' . $e->getMessage()
            ];
        }
    }

    public function deleteRefund(int $id): array
    {
        try {
            $refund = Refund::findOrFail($id);
            $refund->delete();

            $this->createRefundNote($refund, 'Reembolso eliminado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'message' => 'Reembolso eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el reembolso: ' . $e->getMessage()
            ];
        }
    }

    private function validateRefundData(array $data): array
    {
        $rules = [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'sale_id' => 'required|exists:sales,id',
            'refund_method' => 'required|in:' . implode(',', array_keys(Refund::REFUND_METHODS)),
            'products' => 'nullable|array|min:1',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
            'products.*.price_per_unit' => 'required_with:products|numeric|min:0',
        ];
        return validator($data, $rules)->validate();
    }

    private function createProductList(Refund $refund, array $products): void
    {
        foreach ($products as $productData) {
            ProductList::create([
                'listable_type' => ProductList::class,
                'listable_id' => $refund->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'price_per_unit' => $productData['price_per_unit']
            ]);
        }
    }

}
