<?php

namespace App\Services;

use App\Models\BoxBalance;
use Illuminate\Support\Facades\DB;

class BoxBalanceService
{
    public function updateBoxBalance($customer_id, $box_balance_delivered, $box_balance_returned)
    {
        try {

            // Ensure one aggregate record per customer
            DB::beginTransaction();
            $boxBalance = BoxBalance::firstOrCreate(
                ['customer_id' => $customer_id],
                ['delivered_boxes' => 0, 'returned_boxes' => 0]
            );

            if ($box_balance_delivered > 0) {
                // Boxes loaned to customer
                $boxBalance->addDeliveredBoxes((int) $box_balance_delivered);
            }
            if ($box_balance_returned > 0) {
                // Boxes collected back from customer
                $boxBalance->addReturnedBoxes((int) $box_balance_returned);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Saldo de caja actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Error al actualizar saldo de caja: ' . $e->getMessage(),
                'type' => 'error',
            ];
        }
    }
}
