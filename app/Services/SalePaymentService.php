<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalePaymentService
{
    /**
     * Add a new payment to a sale.
     */
    public function addPayment(Sale $sale, array $data): array
    {
        try {
            $validated = $this->validatePaymentData($data, $sale);

            DB::beginTransaction();

            // Set user_id to current user if not provided
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = Auth::id();
            }

            // Create the payment
            $payment = SalePayment::create($validated);

            // Update sale payment status
            $sale->updatePaymentStatus();

            // Create a note about the payment
            $this->createPaymentNote($sale, $payment);

            DB::commit();

            return [
                'success' => true,
                'type' => 'success',
                'message' => 'Pago agregado exitosamente.',
                'payment' => $payment,
                'sale' => $sale->fresh(['payments', 'saleDetails']),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'validation',
                'message' => 'Error de validación en los datos del pago.',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Error al procesar el pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing payment.
     */
    public function updatePayment(SalePayment $payment, array $data): array
    {
        try {
            if (!$this->canEditPayment($payment)) {
                return [
                    'success' => false,
                    'type' => 'authorization',
                    'message' => 'No tienes permisos para editar este pago.',
                ];
            }

            $validated = $this->validatePaymentData($data, $payment->sale, $payment->id);

            DB::beginTransaction();

            $oldAmount = $payment->amount;
            $payment->update($validated);

            // Update sale payment status
            $payment->sale->updatePaymentStatus();

            // Create a note about the payment update
            $this->createPaymentUpdateNote($payment->sale, $payment, $oldAmount);

            DB::commit();

            return [
                'success' => true,
                'type' => 'success',
                'message' => 'Pago actualizado exitosamente.',
                'payment' => $payment,
                'sale' => $payment->sale->fresh(['payments', 'saleDetails']),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'validation',
                'message' => 'Error de validación en los datos del pago.',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Error al actualizar el pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a payment.
     */
    public function deletePayment(SalePayment $payment): array
    {
        try {
            if (!$this->canEditPayment($payment)) {
                return [
                    'success' => false,
                    'type' => 'authorization',
                    'message' => 'No tienes permisos para eliminar este pago.',
                ];
            }

            DB::beginTransaction();

            $sale = $payment->sale;
            $amount = $payment->amount;
            $paymentDate = $payment->payment_date->format('d/m/Y');

            $payment->delete();

            // Update sale payment status
            $sale->updatePaymentStatus();

            // Create a note about the payment deletion
            $this->createPaymentDeletionNote($sale, $amount, $paymentDate);

            DB::commit();

            return [
                'success' => true,
                'type' => 'success',
                'message' => 'Pago eliminado exitosamente.',
                'sale' => $sale->fresh(['payments', 'saleDetails']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Error al eliminar el pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mark a sale as fully paid.
     */
    public function markAsFullyPaid(Sale $sale): array
    {
        try {
            DB::beginTransaction();

            $sale->loadMissing('saleDetails');
            $totalAmount = $sale->saleDetails->sum('total_price');
            $totalPaid = $sale->total_paid;
            $remainingBalance = $totalAmount - $totalPaid;

            if ($remainingBalance <= 0.01) {
                return [
                    'success' => false,
                    'type' => 'validation',
                    'message' => 'La venta ya está completamente pagada.',
                ];
            }

            // Create a payment for the remaining balance
            $paymentData = [
                'sale_id' => $sale->id,
                'amount' => $remainingBalance,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'route_id' => $sale->route_id,
                'user_id' => Auth::id(),
                'notes' => 'Pago completo automático',
            ];

            $payment = SalePayment::create($paymentData);

            // Update sale payment status
            $sale->updatePaymentStatus();

            // Create a note
            $this->createPaymentNote($sale, $payment, 'Marcada como pagada completamente');

            DB::commit();

            return [
                'success' => true,
                'type' => 'success',
                'message' => 'Venta marcada como pagada completamente.',
                'payment' => $payment,
                'sale' => $sale->fresh(['payments', 'saleDetails']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Error al marcar como pagada: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment statistics for a sale.
     */
    public function getSalePaymentStats(Sale $sale): array
    {
        $sale->loadMissing(['payments', 'saleDetails']);

        $totalAmount = $sale->saleDetails->sum('total_price');
        $totalPaid = $sale->total_paid;
        $remainingBalance = $sale->remaining_balance;
        $paymentCount = $sale->payments->count();

        $paymentProgress = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;

        return [
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance,
            'payment_count' => $paymentCount,
            'payment_progress' => round($paymentProgress, 2),
            'is_fully_paid' => $sale->isFullyPaid(),
            'is_overpaid' => $sale->isOverpaid(),
            'overpaid_amount' => $sale->overpaid_amount,
            'last_payment_date' => $sale->payments->max('payment_date'),
        ];
    }

    /**
     * Get payment history for a sale.
     */
    public function getSalePaymentHistory(Sale $sale): array
    {
        return $sale->payments()
            ->with(['user', 'route'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Validate payment data.
     */
    private function validatePaymentData(array $data, Sale $sale, ?int $paymentId = null): array
    {
        $sale->loadMissing('saleDetails');
        $totalAmount = $sale->saleDetails->sum('total_price');
        $currentPaid = $sale->total_paid;

        // If updating, subtract the current payment amount
        if ($paymentId) {
            $currentPayment = SalePayment::find($paymentId);
            if ($currentPayment) {
                $currentPaid -= $currentPayment->amount;
            }
        }

        $maxAllowedPayment = $totalAmount - $currentPaid + 0.01; // Small buffer for rounding

        $rules = [
            'sale_id' => 'required|exists:sales,id',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                "max:{$maxAllowedPayment}",
            ],
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => [
                'required',
                Rule::in(array_keys(SalePayment::PAYMENT_METHODS)),
            ],
            'route_id' => 'nullable|exists:routes,id',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'sale_id.required' => 'La venta es obligatoria.',
            'sale_id.exists' => 'La venta seleccionada no es válida.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto debe ser mayor que 0.',
            'amount.max' => "El monto no puede exceder el saldo pendiente de $" . number_format($maxAllowedPayment, 2),
            'payment_date.required' => 'La fecha de pago es obligatoria.',
            'payment_date.date' => 'La fecha de pago debe ser una fecha válida.',
            'payment_date.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'route_id.exists' => 'La ruta seleccionada no es válida.',
            'user_id.exists' => 'El usuario seleccionado no es válido.',
            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];

        return validator($data, $rules, $messages)->validate();
    }

    /**
     * Create a note about a payment.
     */
    private function createPaymentNote(Sale $sale, SalePayment $payment, ?string $customMessage = null): void
    {
        $user = Auth::user();
        $amount = number_format($payment->amount, 2);
        $method = $payment->payment_method_label;
        $date = $payment->payment_date->format('d/m/Y');

        $content = $customMessage ?? "Pago de ${amount} recibido";
        $content .= " (${method}) el ${date}";

        if ($payment->route) {
            $content .= " en ruta: {$payment->route->title}";
        }

        if ($user) {
            $content .= " por {$user->name}";
        }

        Note::create([
            'notable_type' => Sale::class,
            'notable_id' => $sale->id,
            'user_id' => Auth::id(),
            'content' => $content,
        ]);
    }

    /**
     * Create a note about a payment update.
     */
    private function createPaymentUpdateNote(Sale $sale, SalePayment $payment, float $oldAmount): void
    {
        $user = Auth::user();
        $oldAmountFormatted = number_format($oldAmount, 2);
        $newAmountFormatted = number_format($payment->amount, 2);

        $content = "Pago actualizado de ${oldAmountFormatted} a ${newAmountFormatted}";
        $content .= " el " . now()->format('d/m/Y H:i');

        if ($user) {
            $content .= " por {$user->name}";
        }

        Note::create([
            'notable_type' => Sale::class,
            'notable_id' => $sale->id,
            'user_id' => Auth::id(),
            'content' => $content,
        ]);
    }

    /**
     * Create a note about a payment deletion.
     */
    private function createPaymentDeletionNote(Sale $sale, float $amount, string $paymentDate): void
    {
        $user = Auth::user();
        $amountFormatted = number_format($amount, 2);

        $content = "Pago de ${amountFormatted} del ${paymentDate} eliminado";
        $content .= " el " . now()->format('d/m/Y H:i');

        if ($user) {
            $content .= " por {$user->name}";
        }

        Note::create([
            'notable_type' => Sale::class,
            'notable_id' => $sale->id,
            'user_id' => Auth::id(),
            'content' => $content,
        ]);
    }

    /**
     * Check if a payment can be edited.
     */
    private function canEditPayment(SalePayment $payment): bool
    {
        $user = Auth::user();

        // Admin can edit any payment
        if ($user->role === 'admin') {
            return true;
        }

        // Only allow editing recent payments (within 24 hours)
        $hoursOld = now()->diffInHours($payment->created_at);
        if ($hoursOld > 24) {
            return false;
        }

        // Carriers can only edit payments they created
        if ($user->role === 'carrier') {
            return $payment->user_id === $user->id;
        }

        return false;
    }

    public function searchPayments(
        ?string $startDate = null,
        ?string $endDate = null,
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 10
    ) {
        $query = SalePayment::query();

        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage);
    }
}
