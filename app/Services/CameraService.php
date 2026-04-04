<?php

namespace App\Services;

use App\Models\Camera;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

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

            DB::beginTransaction();

            $camera = Camera::create($validated);

            DB::commit();

            return [
                'success' => true,
                'camera' => $camera,
                'message' => 'Cámara creada exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage(),
                'type' => 'validation',
                'errors' => $e->errors(),
            ]; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return [
                'success' => false,
                'message' => 'Error al crear la cámara: ' . $e->getMessage(),
                'type' => 'error',
            ];
        }
    }

    public function updateCamera(int $id, array $data): array
    {
        try {
            $validated = $this->validateCameraData($data);

            $camera = Camera::findOrFail($id);

            DB::beginTransaction();

            $camera->update($validated);

            DB::commit();

            $this->createCameraNote($camera, 'Cámara "' . $camera->name . '" actualizada el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'camera' => $camera,
                'message' => 'Cámara actualizada exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage(),
                'type' => 'validation',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
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

            $this->createCameraNote($camera, 'Cámara "' . $camera->name . '" eliminada el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

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
