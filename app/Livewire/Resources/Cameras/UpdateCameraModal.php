<?php

namespace App\Livewire\Resources\Cameras;

use Livewire\Component;
use App\Services\CameraService;

class UpdateCameraModal extends Component
{
    public bool $showUpdateModal = false;
    public $selectedCamera = null;

    public string $name = '';
    public string $location = '';
    public int $box_stock = 0;

    public CameraService $cameraService;

    public function __construct(CameraService $cameraService)
    {
        $this->cameraService = $cameraService;
    }

    #[On('open-update-camera-modal')]
    public function openUpdateCameraModal($id)
    {
        $camera = Camera::findOrFail($id);
        $this->selectedCamera = $camera;
        $this->name = $camera->name;
        $this->location = $camera->location;
        $this->box_stock = $camera->box_stock;
        $this->showUpdateModal = true;
    }

    public function updateCamera()
    {
        try {
            $result = $this->cameraService->updateCamera($this->selectedCamera->id, $this->getFormData());
            $this->emit('camera-updated', $result);
        } catch (\Exception $e) {
            $this->emit('camera-update-failed', $e->getMessage());
        }
    }

    public function getFormData()
    {
        return [
            'name' => $this->name,
            'location' => $this->location,
            'box_stock' => $this->box_stock,
        ];
    }

    public function render()
    {
        return view('livewire.resources.cameras.update-camera-modal');
    }
}
