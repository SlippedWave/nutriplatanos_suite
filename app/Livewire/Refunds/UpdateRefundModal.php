<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class UpdateRefundModal extends Component
{
    public bool $showUpdateModal = false;

    public ?Refund $selectedRefund = null;

    public int $refund_id;
    public int $user_id;
    public float $refunded_amount;
    public string $refund_method = '';
    public string $reason = '';

    protected RefundService $refundService;

    public $users = [];
    public array $refund_methods = Refund::REFUND_METHODS;

    public $listeners = [
        'open-update-refund-modal' => 'openUpdateRefundModal' ,
    ];

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function mount()
    {
        $this->users = User::where('active', true)->get();
    }

    public function openUpdateRefundModal(int $refund_id)
    {
        $this->selectedRefund = Refund::findOrFail($refund_id);
        $this->user_id = $this->selectedRefund->user_id;
        $this->refunded_amount = $this->selectedRefund->refunded_amount;
        $this->refund_method = $this->selectedRefund->refund_method;
        $this->reason = $this->selectedRefund->reason;
        $this->resetValidation();
        $this->showUpdateModal = true;
    }

    public function updateRefund(): void
    {
        try {
            $result = $this->refundService->updateRefund($this->selectedRefund, $this->getFormData());

            if ($result['success']) {
                $this->resetValidation();
                $this->dispatch('refunds-info-updated', $result);
                $this->showUpdateModal = false;
                return;
            }

            if (($result['type'] ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($result['validation-errors'] ?? []));
                return;
            }

            $this->dispatch('refund-update-failed', $result['message']);
        } catch (\Exception $e) {
            $this->dispatch('refund-update-failed', $e->getMessage());
        }
    }

    public function getFormData()
    {
        return [
            'user_id' => $this->user_id,
            'sale_id' => $this->selectedRefund->sale_id,
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
