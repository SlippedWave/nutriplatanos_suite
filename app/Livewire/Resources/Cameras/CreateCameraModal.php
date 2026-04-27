<?php

namespace App\Livewire\Resources\Cameras;

use Livewire\Component;
use App\Services\CameraService;
use Illuminate\Support\MessageBag;

class CreateCameraModal extends Component
{
    public bool $showCreateModal = false;

    public string $name = '';
    public string $location = '';
    public int $box_stock = 0;

    protected CameraService $cameraService;

    // listen for Livewire event
    protected $listeners = [
        'open-create-camera-modal' => 'openCreateCameraModal',
    ];

    public function boot()
    {
        $this->cameraService = app(CameraService::class);
    }

    public function openCreateCameraModal()
    {
        $this->reset(['name', 'location', 'box_stock']);
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function createCamera()
    {
        try {
            $response = $this->cameraService->createCamera($this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Cámara creada exitosamente'
                : 'Error al crear cámara');
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
                $this->showCreateModal = false;
                return;
            }

            if (($type ?? 'error') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }
            
            return;     
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Error al crear cámara: ' . $e->getMessage(),
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
        return view('livewire.resources.cameras.create-camera-modal');
    }
}
