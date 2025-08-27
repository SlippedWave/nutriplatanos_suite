<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;


class CreateRefundModal extends Component
{
    public bool $showCreateModal = false;

    public int $user_id;
    public int $sale_id;
    public float $refunded_amount;
    public string $refund_method = '';
    public string $reason = '';

    protected RefundService $refundService;

    public $users = [];
    public array $refund_methods = Refund::REFUND_METHODS;

    public function mount(?int $user_id = null, ?int $sale_id = null)
    {
        $this->users = User::where('active', true)->get();
        $this->user_id = $user_id ?? Auth::user()->id;
        $this->sale_id = $sale_id;
    }

    protected $listeners = [
        'open-create-refund-modal' => 'openCreateRefundModal',
    ];

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function openCreateRefundModal()
    {
        $this->showCreateModal = true;
        $this->reset(['user_id', 'sale_id', 'refunded_amount', 'refund_method', 'reason']);
    }

    public function createRefund()
    {
        try {
            $this->refundService->createRefund($this->getFormData());
            $this->dispatch('refunds-info-updated');
            $this->showCreateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('refund-creation-failed', $e->getMessage());
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
        return view('livewire.refunds.create-refund-modal');
    }
}
