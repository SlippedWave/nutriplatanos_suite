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
            $result = $this->cameraService->createCamera($this->getFormData());

            if ($result['success']) {
                $this->resetValidation();
                $this->dispatch('cameras-info-updated', $result);
                $this->showCreateModal = false;
                return;
            }

            if (($result['type'] ?? 'error') === 'validation') {
                $this->setErrorBag(new MessageBag($result['errors'] ?? []));
                return;
            }

            $this->dispatch('camera-creation-failed', $result['message']);
        } catch (\Exception $e) {
            $this->dispatch('camera-creation-failed', $e->getMessage());
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
