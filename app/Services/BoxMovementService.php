<?php

namespace App\Services;

use App\Models\BoxMovement;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoxMovementService
{
    /**
     * Create a new box movement entry
     */
    public function createBoxMovement(array $data): array
    {
        try {
            $validated = $this->validateBoxMovementData($data);

            DB::beginTransaction();

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
            DB::rollBack();
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
            'movement_type' => 'required|in:' . implode(',', BoxMovement::MOVEMENT_TYPES),
            'quantity' => 'required|integer|min:1',
            'box_content_status' => 'required|in:' . implode(',', BoxMovement::BOX_CONTENT_STATUSES),
            'moved_at' => 'required|date',
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * Update an existing box movement entry
     */
    public function updateBoxMovement(BoxMovement $boxMovement, array $data): BoxMovement
    {
        $boxMovement->update($data);
        return $boxMovement;
    }

    /**
     * Delete a box movement entry
     */
    public function deleteBoxMovement(BoxMovement $boxMovement): void
    {
        $boxMovement->delete();
    }
}
