<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Route;
use App\Models\SalePayment;
use App\Services\SalePaymentService;
use App\Services\SaleService;
use Illuminate\Support\MessageBag;
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
        $this->customer_id = $this->contextCustomerId ?? null;
        $this->route_id = $this->contextRouteId ?? null;
        $this->resetValidation();
        $this->reset([
            'payment_status',
            'paid_amount',
            'notes',
            'saleProducts',
            'box_balance_delivered',
            'box_balance_returned'
            ]);
            $this->dispatch('add-product');
        $this->showCreateModal = true;
    }

    public function clearErrorsForModal()
    {
        session()->forget(['error', 'message']);
    }

    public function createSale()
    {
        try {
            $response = $this->saleService->createSale($this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Venta creada exitosamente'
                : 'Error al crear la venta');
            $type = $success ? 'success' : ($response['type'] ?? 'error');
             
            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'sales',
            ]);

            if ($success) {
                $this->resetValidation();
                $this->dispatch('refresh-sales-table');
                $this->showCreateModal = false;
                return;
            }

            if (($type ?? 'error') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($response['validation-errors'] ?? []));
                return;
            }

            return;
        } catch (\Exception $e) {
            // Livewire validation failed, don't close modal
            $this->dispatch('show-message-banner', [
                'text' => 'Ocurrió un error inesperado al crear la venta: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'sales',
            ]);
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
            'total_amount_excluding_refunds' => $totalAmount,
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
