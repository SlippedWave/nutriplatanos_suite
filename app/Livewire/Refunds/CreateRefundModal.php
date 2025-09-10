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

    public ?int $user_id = null;
    public ?int $sale_id = null;
    public ?float $refunded_amount = null;
    public string $refund_method = '';
    public string $reason = '';
    public array $refund_products = [];

    protected RefundService $refundService;

    public $users = [];
    public array $refund_methods = Refund::REFUND_METHODS;

    public function mount()
    {
        $this->users = User::where('active', true)->get();
    }

    protected $listeners = [
        'open-create-refund-modal' => 'openCreateRefundModal',
    ];

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function openCreateRefundModal(int $user_id, int $sale_id)
    {
        $this->showCreateModal = true;
        $this->reset(['user_id', 'sale_id', 'refunded_amount', 'refund_method', 'reason']);
        $this->user_id = $user_id;
        $this->sale_id = $sale_id;
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
        $validProducts = array_filter($this->refund_products, function ($product) {
            return isset($product['product_id']) && $product['quantity'] > 0 && $product['quantity'] > 0;
        });

        /*
        $totalAmount = array_reduce($validProducts, function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['price_per_unit']);
        }, 0.00);
        */


        return [
            'user_id' => $this->user_id,
            'sale_id' => $this->sale_id,
            'products' => array_values($validProducts),
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
