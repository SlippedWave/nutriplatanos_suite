<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Livewire\Component;


class CreateRefundModal extends Component
{
    public bool $showCreateModal = false;

    public bool $showErrorDialog = false;
    public int $user_id;
    public ?int $sale_id = null;
    public ?float $refunded_amount = null;
    public string $reason = '';
    public array $refund_products = [];
    public string $refund_method = '';

    protected RefundService $refundService;

    public $users = [];
    public array $refund_methods = Refund::REFUND_METHODS;

    public function mount()
    {
        $this->users = User::where('active', true)->get();
    }

    protected $listeners = [
        'open-create-refund-modal' => 'openCreateRefundModal',
        'refund-creation-failed' => 'showError',
    ];

    public function showError($message)
    {
        session()->flash('error', $message);
    }

    public function boot()
    {
        $this->refundService = app(RefundService::class);
    }

    public function openCreateRefundModal(int $sale_id)
    {
        $this->showCreateModal = true;
        $this->reset(['user_id', 'sale_id', 'refunded_amount', 'reason']);
        $this->refund_method = array_key_first(Refund::REFUND_METHODS);
        $this->user_id = Auth::id();
        $this->sale_id = $sale_id;
        $this->resetValidation();
        
    }

    public function createRefund()
    {
        try {
            $response = $this->refundService->createRefund($this->getFormData());

            if ($response['success']) {
                $this->resetValidation();
                $this->dispatch('refunds-info-updated', $response);
                $this->showCreateModal = false;
                return;
            }

            if (($response['type'] ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                $this->dispatch('refund-creation-failed', $response['message'] ?? 'Refund creation failed');
                return;
            }

        } catch (\Exception $e) {
            $this->dispatch('refund-creation-failed', $e->getMessage());
        }
    }

    public function getFormData()
    {
        $validProducts = array_filter($this->refund_products, function ($product) {
            return isset($product['product_id']) && $product['quantity'] > 0 && $product['price_per_unit'] > 0;
        });

        $totalAmount = array_reduce($validProducts, function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['price_per_unit']);
        }, 0.00);

        if ($this->refund_method === 'product') {
            $this->refunded_amount = $totalAmount;
        }

        return [
            'user_id' => $this->user_id ?? Auth::user()->id,
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
