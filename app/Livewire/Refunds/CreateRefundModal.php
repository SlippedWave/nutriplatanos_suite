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
    }

    public function createRefund()
    {
        try {
            $response = $this->refundService->createRefund($this->getFormData());

            if (!($response['success'] ?? false)) {
                // Surface validation errors in the modal
                if (($response['type'] ?? null) === 'validation' && isset($response['errors'])) {
                    foreach ($response['errors'] as $field => $messages) {
                        foreach ((array) $messages as $msg) {
                            $this->addError($field, $msg);
                        }
                    }
                }
                session()->flash('error', $response['message'] ?? __('Error al crear el reembolso.'));
                return;
            }

            // Notify listeners and close
            $this->dispatch('refund-created');
            $this->dispatch('refresh-sales-table');
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
