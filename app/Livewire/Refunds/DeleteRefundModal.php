<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use App\Services\RefundService;
use Livewire\Component;

class DeleteRefundModal extends Component
{
    public bool $showDeleteModal = false;
    public ?Refund $selectedRefund = null;

    protected RefundService $refundService;

    protected $listeners = [
        'open-delete-refund-modal' => 'openDeleteRefundModal',
    ];

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function openDeleteRefundModal(int $id)
    {
        $this->selectedRefund = Refund::find($id);
        $this->showDeleteModal = true;
    }

    public function deleteRefund(): void
    {
        try {
            if ($this->selectedRefund) {
                $this->refundService->deleteRefund($this->selectedRefund->id);
                $this->dispatch('refunds-info-updated');
                $this->showDeleteModal = false;
            }
        } catch (\Exception $e) {
            $this->dispatch('refund-deletion-failed', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.refunds.delete-refund-modal');
    }
}
