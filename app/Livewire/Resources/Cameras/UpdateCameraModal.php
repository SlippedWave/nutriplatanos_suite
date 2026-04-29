<?php

namespace App\Livewire\Resources\Cameras;

use Livewire\Component;
use App\Services\CameraService;
use App\Models\Camera;
use Illuminate\Support\MessageBag;

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
        $this->resetValidation();
        $this->showUpdateModal = true;
    }

    public function updateCamera()
    {
        try {
            $response = $this->cameraService->updateCamera($this->selectedCameraId, $this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Cámara actualizada exitosamente'
                : 'Error al actualizar cámara');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'cameras',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('cameras-info-updated', $response);
                $this->showUpdateModal = false;
                return;
            }

             if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al actualizar cámara: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'cameras',
            ]);
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
