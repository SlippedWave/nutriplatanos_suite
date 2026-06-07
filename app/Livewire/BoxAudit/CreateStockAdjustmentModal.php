<?php

namespace App\Livewire\BoxAudit;

use App\Models\Camera;
use App\Services\CameraStockAdjustmentService;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateStockAdjustmentModal extends Component
{
    public bool $showModal = false;

    public string $camera_id = '';
    public string $direction = 'in';
    public int $quantity = 0;
    public string $reason = '';

    protected CameraStockAdjustmentService $service;

    public function boot(): void
    {
        $this->service = app(CameraStockAdjustmentService::class);
    }

    #[On('open-stock-adjustment-modal')]
    public function openModal(?int $cameraId = null): void
    {
        $this->resetValidation();
        $this->camera_id = $cameraId ? (string) $cameraId : '';
        $this->direction = 'in';
        $this->quantity  = 0;
        $this->reason    = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $signedQuantity = $this->direction === 'in' ? abs($this->quantity) : -abs($this->quantity);

        $response = $this->service->createAdjustment([
            'camera_id' => $this->camera_id,
            'quantity'  => $signedQuantity,
            'reason'    => $this->reason ?: null,
        ]);

        $success = $response['success'] ?? false;
        $type    = $success ? 'success' : ($response['type'] ?? 'exception');

        $this->dispatch('show-message-banner', [
            'text'     => $response['message'],
            'type'     => $type,
            'duration' => 5000,
            'bannerId' => 'box-audit',
        ]);

        if (!$success) {
            if (isset($response['validation-errors'])) {
                foreach ($response['validation-errors'] as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }
            }
            return;
        }

        $this->dispatch('box-audit-updated');
        $this->showModal = false;
    }

    public function render()
    {
        $cameras = Camera::orderBy('name')->get();
        return view('livewire.box-audit.create-stock-adjustment-modal', compact('cameras'));
    }
}
