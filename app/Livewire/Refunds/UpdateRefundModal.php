<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Livewire\Component;

class UpdateRefundModal extends Component
{
    public bool $showUpdateModal = false;

    public ?int $selectedRefundId = null;

    public int $user_id;
    public int $sale_id;
    public float $refunded_amount;
    public string $refund_method = '';
    public string $reason = '';

    protected RefundService $refundService;

    public $users = [];
    public array $refund_methods = Refund::REFUND_METHODS;

    public $listeners = [
        'open-update-refund-modal' => 'openUpdateRefundModal',
    ];

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function mount()
    {
        $this->users = User::where('active', true)->get();
    }

    public function openUpdateRefundModal($id)
    {
        $refund = Refund::findOrFail($id);
        $this->selectedRefundId = $refund->id;
        $this->user_id = $refund->user_id;
        $this->sale_id = $refund->sale_id;
        $this->refunded_amount = $refund->refunded_amount;
        $this->refund_method = $refund->refund_method;
        $this->reason = $refund->reason;
        $this->showUpdateModal = true;
    }

    public function updateRefund(): void
    {
        try {
            $this->refundService->updateRefund($this->selectedRefundId, $this->getFormData());
            $this->dispatch('refunds-info-updated');
            $this->showUpdateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('refund-update-failed', $e->getMessage());
        }
    }

    public function getFormData()
    {
        return [
            'user_id' => $this->user_id,
            'sale_id' => $this->sale_id,
            'refunded_amount' => $this->refunded_amount,
            'refund_method' => $this->refund_method,
            'reason' => $this->reason,
        ];
    }

    public function render()
    {
        return view('livewire.refunds.update-refund-modal');
    }
}
