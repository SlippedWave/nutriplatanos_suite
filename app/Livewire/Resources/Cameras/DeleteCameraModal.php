<?php

namespace App\Livewire\Resources\Cameras;

use Livewire\Component;
use App\Services\CameraService;
use App\Models\Camera;

class DeleteCameraModal extends Component
{
    public bool $showDeleteModal = false;
    public ?Camera $selectedCamera = null;

    protected CameraService $cameraService;

    protected $listeners = [
        'open-delete-camera-modal' => 'openDeleteCameraModal',
    ];

    public function boot()
    {
        $this->cameraService = app(CameraService::class);
    }

    public function openDeleteCameraModal(int $id)
    {
        $this->selectedCamera = Camera::find($id);
        $this->showDeleteModal = true;
    }

    public function deleteCamera(): void
    {
        try {
            $response = $this->cameraService->deleteCamera($this->selectedCamera->id);

            $success = $response['success'] ?? false;

            $message = $response['message'] ?? ($success
                ? 'Cámara eliminada exitosamente'
                : 'Error al eliminar cámara');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'cameras',
            ]);

            if ($success) {
                $this->dispatch('cameras-info-updated');
                $this->showDeleteModal = false;
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al eliminar cámara: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'cameras',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.resources.cameras.delete-camera-modal');
    }
}
