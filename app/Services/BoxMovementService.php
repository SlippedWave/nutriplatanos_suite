<?php

namespace App\Services;

use App\Models\BoxMovement;
use App\Models\Note;
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
                    'message' => 'Datos inválidos para el movimiento de caja.',
                    'errors' => $ve->errors(),
                    'type' => 'error',
                ];
            }

            $boxMovementData = collect($validated)->except('notes')->toArray();
            $boxMovement = BoxMovement::create($boxMovementData);

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
                'type' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear movimiento de caja: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    }

    private function validateBoxMovementData(array $data): array
    {
        $rules = [
            'camera_id' => 'required|exists:cameras,id',
            'route_id' => 'required|exists:routes,id',
            'movement_type' => 'required|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'quantity' => 'required|integer|min:1',
            'box_content_status' => 'required|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
            'moved_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];

        return validator($data, $rules)->validate();
    }
}
