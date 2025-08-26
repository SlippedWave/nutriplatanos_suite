<?php

namespace App\Services;

use App\Models\Camera;

class CameraService
{
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

    private function updateCamera(int $id, array $data): array
    {
        try {
            $validated = $this->validateCameraData($data);

            $camera = Camera::findOrFail($id);
            $camera->update($validated);

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

    private function deleteCamera(int $id): array
    {
        try {
            $camera = Camera::findOrFail($id);
            $camera->delete();

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