<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Refund;
use Illuminate\Support\Facades\Auth;

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

            return [
                'success' => true,
                'refund' => Refund::create($validated),
                'message' => 'Reembolso creado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el reembolso: ' . $e->getMessage()
            ];
        }
    }

    public function updateRefund(int $id, array $data): array
    {
        try {
            $validated = $this->validateRefundData($data);

            $refund = Refund::findOrFail($id);
            $refund->update($validated);

            $this->createRefundNote($refund, 'Reembolso actualizado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

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
        ];
        return validator($data, $rules)->validate();
    }
}
