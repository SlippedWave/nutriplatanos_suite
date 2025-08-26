<?php

namespace App\Services;

use App\Models\Camera;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;


class CameraService
{
    public function createCameraNote(Camera $camera, string $noteContent): void
    {
        Note::create([
            'notable_id' => $camera->id,
            'notable_type' => Camera::class,
            'content' => $noteContent,
            'user_id' => Auth::id(),
            'type' => 'camera'
        ]);
    }


    public function createCamera(array $data): array
    {
        try {
            $validated = $this->validateCameraData($data);

            return [
                'success' => true,
                'camera' => Camera::create($validated),
                'message' => 'Cámara creada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la cámara: ' . $e->getMessage()
            ];
        }
    }

    public function updateCamera(int $id, array $data): array
    {
        try {
            $validated = $this->validateCameraData($data);

            $camera = Camera::findOrFail($id);
            $camera->update($validated);

            $this->createCameraNote($camera, 'Cámara actualizada el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'camera' => $camera,
                'message' => 'Cámara actualizada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la cámara: ' . $e->getMessage()
            ];
        }
    }

    public function deleteCamera(int $id): array
    {
        try {
            $camera = Camera::findOrFail($id);
            $camera->delete();

            $this->createCameraNote($camera, 'Cámara eliminada el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'message' => 'Cámara eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la cámara: ' . $e->getMessage()
            ];
        }
    }

    private function validateCameraData(array $data): array
    {
        // Aquí puedes agregar la lógica de validación que necesites
        $rules = [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'box_stock' => 'required|integer|min:0',
        ];

        return validator($data, $rules)->validate();
    }
}