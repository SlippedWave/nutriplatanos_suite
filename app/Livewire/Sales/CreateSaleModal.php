<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Route;
use App\Models\SalePayment;
use App\Services\SalePaymentService;
use App\Services\SaleService;
use Livewire\Component;

class CreateSaleModal extends Component
{
    public bool $showCreateModal = false;

    public ?int $contextRouteId = null;
    public ?int $contextCustomerId = null;

    public $customers = [];
    public $routes = [];
    public array $paymentMethods = [];

    // Form fields
    public $customer_id = null;
    public $route_id = null;
    public $payment_status = 'pending';
    public $paid_amount = 0.00;
    public $notes = '';
    public $saleProducts = [];
    public int $box_balance_delivered = 0;
    public int $box_balance_returned = 0;

    public $payment_amount = 0.00;
    public $payment_date = '';
    public $payment_method = 'cash';

    protected SaleService $saleService;
    protected SalePaymentService $salePaymentService;

    protected $messages = [
        'customer_id.required' => 'El cliente es obligatorio.',
        'customer_id.exists' => 'El cliente seleccionado no es válido.',
        'route_id.required' => 'La ruta es obligatoria.',
        'route_id.exists' => 'La ruta seleccionada no es válida.',
        'paid_amount.numeric' => 'El monto pagado debe ser un número.',
        'paid_amount.min' => 'El monto pagado no puede ser negativo.',
        'paid_amount.max' => 'El monto pagado es demasiado alto.',
        'saleProducts.*.product_id.required' => 'Debe seleccionar un producto.',
        'saleProducts.*.product_id.exists' => 'El producto seleccionado no es válido.',
        'saleProducts.*.quantity.required' => 'La cantidad es obligatoria.',
        'saleProducts.*.quantity.integer' => 'La cantidad debe ser un número entero.',
        'saleProducts.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
        'saleProducts.*.price_per_unit.required' => 'El precio unitario es obligatorio.',
        'saleProducts.*.price_per_unit.numeric' => 'El precio unitario debe ser un número.',
        'saleProducts.*.price_per_unit.min' => 'El precio unitario debe ser mayor que 0.',
    ];

    protected $listeners = [
        'open-create-sale-modal' => 'openCreateSaleModal',
    ];

    public function boot()
    {
        $this->saleService = app(SaleService::class);
        $this->salePaymentService = app(SalePaymentService::class);
    }

    public function mount(?int $contextRouteId = null, ?int $contextCustomerId = null)
    {
        $this->contextRouteId = $contextRouteId;
        $this->contextCustomerId = $contextCustomerId;

        $this->customers = Customer::where('active', true)->get();
        $this->routes = Route::where('status', 'active')->get();
        $this->paymentMethods = SalePayment::PAYMENT_METHODS;

        $this->route_id = $contextRouteId;
        $this->customer_id = $contextCustomerId;
    }

    public function openCreateSaleModal()
    {
        $this->showCreateModal = true;
        $this->customer_id = $this->contextCustomerId ?? null;
        $this->route_id = $this->contextRouteId ?? null;
        $this->reset([
            'payment_status',
            'paid_amount',
            'notes',
            'saleProducts',
            'box_balance_delivered',
            'box_balance_returned'
        ]);
        $this->dispatch('add-product');
    }

    public function clearErrorsForModal()
    {
        session()->forget(['error', 'message']);
    }

    public function createSale()
    {
        try {
            $result = $this->saleService->createSale($this->getFormData());

            if ($result['success']) {
                $sale = $result['sale'] ?? null;

                // Create a payment record if the sale has a paid amount
                if ($sale && ($this->payment_status === 'paid' || $this->payment_status === 'partial')) {
                    $paymentAmount = $this->payment_status === 'paid' ? $sale->total_amount : $this->paid_amount;

                    $paymentData = [
                        'sale_id' => $sale->id,
                        'amount' => $paymentAmount,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => $this->payment_method, // Default payment method
                        'route_id' => $this->route_id ?: null,
                        'notes' => 'Pago inicial registrado con la venta',
                    ];

                    $this->salePaymentService->addPayment($sale, $paymentData);
                }
                $this->dispatch('refresh-sales-table');
                $this->dispatch('show-sales-table-message', $result);
                $this->showCreateModal = false;
            } else {
                // Handle different types of errors
                switch ($result['type'] ?? 'error') {
                    case 'validation':
                        // Don't close modal for validation errors
                        if (isset($result['errors'])) {
                            // Set validation errors for display
                            foreach ($result['errors'] as $field => $messages) {
                                $this->addError($field, implode(' ', $messages));
                            }
                        }
                        $this->dispatch('flash-sales-table-message', $result['message'], 'error');
                        break;
                    default:
                        // Don't close modal for other errors, let user retry
                        $this->dispatch('show-sales-table-message', $result);
                        break;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Livewire validation failed, don't close modal
            $this->dispatch('flash-sales-table-message', $e->getMessage(), 'error');
        }
    }

    private function getFormData(): array
    {
        // Filter out empty products
        $validProducts = array_filter($this->saleProducts, function ($product) {
            return !empty($product['product_id']) && $product['quantity'] > 0 && $product['price_per_unit'] > 0;
        });

        $totalAmount = array_reduce($validProducts, function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['price_per_unit']);
        }, 0.00);

        return [
            'customer_id' => $this->customer_id,
            'route_id' => $this->route_id,
            'payment_status' => $this->payment_status,
            'total_amount' => $totalAmount,
            'net_amount_due' => $totalAmount,
            'paid_amount' => match ($this->payment_status) {
                'partial' => $this->paid_amount,
                'paid' => $totalAmount,
                default => 0,
            },
            'notes' => $this->notes,
            'products' => array_values($validProducts), // Re-index
            'box_balance_returned' => $this->box_balance_returned,
            'box_balance_delivered' => $this->box_balance_delivered,
        ];
    }

    public function render()
    {
        return view('livewire.sales.create-sale-modal');
    }
}
