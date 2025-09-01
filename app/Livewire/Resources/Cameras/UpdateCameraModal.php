<?php

namespace App\Livewire\Resources\Cameras;

use Livewire\Component;
use App\Services\CameraService;
use App\Models\Camera;

class UpdateCameraModal extends Component
{
    public bool $showUpdateModal = false;

    public ?int $selectedCameraId = null;

    public string $name = '';
    public string $location = '';
    public int $box_stock = 0;

    protected CameraService $cameraService;

    // listen for Livewire event
    protected $listeners = [
        'open-update-camera-modal' => 'openUpdateCameraModal',
    ];

    public function boot()
    {
        $this->cameraService = app(CameraService::class);
    }

    public function openUpdateCameraModal($id)
    {
        $camera = Camera::findOrFail($id);
        $this->selectedCameraId = $camera->id;
        $this->name = $camera->name;
        $this->location = $camera->location;
        $this->box_stock = $camera->box_stock;
        $this->showUpdateModal = true;
    }

    public function updateCamera()
    {
        try {
            $result = $this->cameraService->updateCamera($this->selectedCameraId, $this->getFormData());
            $this->dispatch('cameras-info-updated', $result);
            $this->showUpdateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('camera-update-failed', $e->getMessage());
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
