<?php

namespace App\Services;

use App\Models\BoxBalance;

class BoxBalanceService
{
    public function updateBoxBalance($customer_id, $box_balance_delivered, $box_balance_returned)
    {
        try {

            // Ensure one aggregate record per customer
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


            return [
                'success' => true,
                'message' => 'Saldo de caja actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar saldo de caja: ' . $e->getMessage()
            ];
        }
    }
}
