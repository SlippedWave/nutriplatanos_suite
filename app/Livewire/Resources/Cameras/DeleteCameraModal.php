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
            if ($this->selectedCamera) {
                $this->cameraService->deleteCamera($this->selectedCamera->id);
                $this->dispatch('cameras-info-updated');
                $this->showDeleteModal = false;
            }
        } catch (\Exception $e) {
            $this->dispatch('camera-deletion-failed', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.resources.cameras.delete-camera-modal');
    }
}
