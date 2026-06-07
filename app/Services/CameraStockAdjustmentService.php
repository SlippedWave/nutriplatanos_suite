<?php

namespace App\Services;

use App\Models\Camera;
use App\Models\CameraStockAdjustment;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CameraStockAdjustmentService
{
    public function createAdjustment(array $data): array
    {
        try {
            $validated = validator($data, [
                'camera_id' => ['required', 'exists:cameras,id'],
                'quantity'  => ['required', 'integer', 'not_in:0'],
                'reason'    => ['nullable', 'string', 'max:500'],
            ])->validate();

            DB::beginTransaction();

            $adjustment = CameraStockAdjustment::create([
                'camera_id' => $validated['camera_id'],
                'user_id'   => Auth::id(),
                'quantity'  => $validated['quantity'],
                'reason'    => $validated['reason'] ?? null,
            ]);

            $camera = Camera::find($validated['camera_id']);
            Note::create([
                'user_id'      => Auth::id(),
                'content'      => 'Ajuste manual de stock: ' . ($validated['quantity'] > 0 ? '+' : '') . $validated['quantity']
                    . ($validated['reason'] ? ' — ' . $validated['reason'] : ''),
                'type'         => 'camera',
                'notable_id'   => $camera->id,
                'notable_type' => Camera::class,
            ]);

            DB::commit();

            return [
                'success'    => true,
                'message'    => 'Ajuste registrado exitosamente.',
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
