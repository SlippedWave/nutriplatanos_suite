<?php

namespace App\Livewire\Sales;

use App\Models\Route;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Services\SalePaymentService;
use Livewire\Component;

class AddPaymentModal extends Component
{
    public bool $showAddPaymentModal = false;
    public ?Sale $selectedSale = null;

    protected function getPaymentRules(): array
    {
        return [
            'payment_amount' => 'required|numeric|min:0.01|max:999999.99',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,transfer,check,card,other',
            'payment_route_id' => 'nullable|exists:routes,id',
            'payment_notes' => 'nullable|string|max:1000',
        ];
    }

    public $paymentMethods = [];
    public $routes = [];

    /**
     * Get payment validation messages.
     */
    protected function getPaymentMessages(): array
    {
        return [
            'payment_amount.required' => 'El monto del pago es obligatorio.',
            'payment_amount.numeric' => 'El monto del pago debe ser un número.',
            'payment_amount.min' => 'El monto del pago debe ser mayor que 0.',
            'payment_amount.max' => 'El monto del pago es demasiado alto.',
            'payment_date.required' => 'La fecha de pago es obligatoria.',
            'payment_date.date' => 'La fecha de pago debe ser una fecha válida.',
            'payment_date.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'payment_route_id.exists' => 'La ruta seleccionada no es válida.',
            'payment_notes.string' => 'Las notas deben ser texto.',
            'payment_notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    public $payment_amount = 0.00;
    public $payment_date = '';
    public $payment_method = 'cash';
    public $payment_route_id = '';
    public $payment_notes = '';
    public $contextRouteId = null;

    protected SalePaymentService $salePaymentService;

    public $listeners = [
        'open-add-payment-modal' => 'openAddPaymentModal',
    ];

    public function boot()
    {
        $this->salePaymentService = app(SalePaymentService::class);
    }

    public function mount($contextRouteId)
    {
        $this->routes = Route::where('status', 'active')->get();
        $this->paymentMethods = SalePayment::PAYMENT_METHODS;
        $this->contextRouteId = $contextRouteId;
    }

    public function openAddPaymentModal(int $saleId)
    {
        $this->showAddPaymentModal = true;
        $this->selectedSale = Sale::with(['productList', 'payments'])->findOrFail($saleId);
        $this->payment_date = now()->toDateString();
        $this->payment_route_id = $this->contextRouteId ?? '';
        $this->reset([
            'payment_amount',
            'payment_method',
            'payment_notes',
        ]);
        session()->forget(['error', 'message']);
    }

    public function addPayment()
    {
        if (!$this->selectedSale) {
            $this->dispatch(
                'flash-sales-table-message',
                'No se ha seleccionado ninguna venta.',
                'error'
            );
            return;
        }

        try {
            // Validate payment data
            $this->validate($this->getPaymentRules(), $this->getPaymentMessages());

            $result = $this->salePaymentService->addPayment($this->selectedSale, $this->getPaymentFormData());

            if ($result['success']) {
                $this->showAddPaymentModal = false;
                $this->dispatch('refresh-sales-table');
                $this->dispatch('show-sales-table-message', $result);
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
                        $this->dispatch(
                            'flash-sales-table-message',
                            $result['message'],
                            'error'
                        );
                        break;
                    default:
                        // Don't close modal for other errors, let user retry
                        $this->dispatch('show-sales-table-message', $result);
                        break;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Livewire validation failed, don't close modal
            $this->dispatch(
                'flash-sales-table-message',
                $e->getMessage(),
                'error'
            );
        }
    }

    private function getPaymentFormData(): array
    {
        return [
            'sale_id' => $this->selectedSale->id,
            'amount' => $this->payment_amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'route_id' => $this->payment_route_id ?: null,
            'notes' => $this->payment_notes,
        ];
    }

    public function markAsFullyPaid($saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $result = $this->salePaymentService->markAsFullyPaid($sale);

        $this->showSalesTableMessage($result);
    }


    public function render()
    {
        return view('livewire.sales.add-payment-modal');
    }
}
