<?php

namespace App\Services;

use App\Models\BoxMovement;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoxMovementService
{
    public function createBoxMovement(array $data): array
    {
        try {
            if (empty($data['moved_at'])) {
                $data['moved_at'] = now();
            }

            try {
                $validated = $this->validateBoxMovementData($data);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return [
                    'success' => false,
                    'message' => 'Datos inválidos para el movimiento de caja. Hay ' . count($ve->errors()) . ' error(es).',
                    'validation-errors' => $ve->errors(),
                    'type' => 'validation-exception',
                ];
            }

            DB::beginTransaction();
            $boxMovementData = collect($validated)->except('notes')->toArray();
            $boxMovement = BoxMovement::create($boxMovementData);
            DB::commit();

            if (!empty($validated['notes'])) {
                Note::create([
                    'notable_id' => $boxMovement->id,
                    'notable_type' => BoxMovement::class,
                    'user_id' => Auth::id(),
                    'content' => $validated['notes'],
                ]);
            }

            return [
                'success' => true,
                'boxMovement' => $boxMovement,
                'message' => 'Movimiento de caja creado exitosamente.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear movimiento de caja: ' . $e->getMessage(),
                'type' => 'exception',
            ];
        }
    }

    private function validateBoxMovementData(array $data): array
    {
        $cameraRequired = in_array($data['movement_type'] ?? '', ['warehouse_to_route', 'route_to_warehouse']);

        $rules = [
            'camera_id'          => $cameraRequired ? 'required|exists:cameras,id' : 'nullable|exists:cameras,id',
            'route_id'           => 'required|exists:routes,id',
            'movement_type'      => 'required|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'quantity'           => 'required|integer|min:1',
            'box_content_status' => 'required|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
            'moved_at'           => 'required|date',
            'notes'              => 'nullable|string|max:1000',
        ];

        return validator($data, $rules)->validate();
    }
}
