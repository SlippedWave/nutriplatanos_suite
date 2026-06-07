<?php

namespace App\Services;

use App\Models\BoxBalanceAdjustment;
use App\Models\Customer;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BoxBalanceAdjustmentService
{
    public function createAdjustment(array $data): array
    {
        try {
            $validated = validator($data, [
                'customer_id' => ['required', 'exists:customers,id'],
                'quantity'    => ['required', 'integer', 'not_in:0'],
                'reason'      => ['nullable', 'string', 'max:500'],
            ])->validate();

            DB::beginTransaction();

            $adjustment = BoxBalanceAdjustment::create([
                'customer_id' => $validated['customer_id'],
                'user_id'     => Auth::id(),
                'quantity'    => $validated['quantity'],
                'reason'      => $validated['reason'] ?? null,
            ]);

            $customer = Customer::find($validated['customer_id']);
            Note::create([
                'user_id'      => Auth::id(),
                'content'      => 'Ajuste manual de cajas: ' . ($validated['quantity'] > 0 ? '+' : '') . $validated['quantity']
                    . ($validated['reason'] ? ' — ' . $validated['reason'] : ''),
                'type'         => 'customer',
                'notable_id'   => $customer->id,
                'notable_type' => Customer::class,
            ]);

            DB::commit();

            return [
                'success'    => true,
                'message'    => 'Ajuste de cajas registrado exitosamente.',
                'adjustment' => $adjustment,
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success'           => false,
                'message'           => 'Error de validación.',
                'validation-errors' => $e->errors(),
                'type'              => 'validation-exception',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al registrar ajuste: ' . $e->getMessage(),
                'type'    => 'exception',
            ];
        }
    }
}
