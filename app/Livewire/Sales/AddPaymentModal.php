<?php

namespace App\Livewire\Sales;

use App\Models\Route;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Services\SalePaymentService;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class AddPaymentModal extends Component
{
    public bool $showAddPaymentModal = false;
    public ?Sale $selectedSale = null;

    protected function getPaymentRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01|max:999999.99',
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
            'amount.required' => 'El monto del pago es obligatorio.',
            'amount.numeric' => 'El monto del pago debe ser un número.',
            'amount.min' => 'El monto del pago debe ser mayor que 0.',
            'amount.max' => 'El monto del pago es demasiado alto.',
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

    public $amount = 0.00;
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

    public function openAddPaymentModal($saleId)
    {
        $this->showAddPaymentModal = true;
        $this->selectedSale = Sale::with(['productList', 'payments'])->findOrFail($saleId);
        $this->payment_date = now()->toDateString();
        $this->payment_route_id = $this->contextRouteId ?? '';
        $this->reset([
            'amount',
            'payment_method',
            'payment_notes',
        ]);
        $this->resetValidation();
        session()->forget(['error', 'message']);
    }

    public function addPayment()
    {
        if (!$this->selectedSale) {
            $this->dispatch(
                'show-message-banner',
                [
                    'text' => 'No se ha seleccionado ninguna venta.',
                    'type' => 'error',
                    'duration' => 5000,
                    'bannerId' => 'sales',
                ]
            );
            return;
        }

        try {
            $result = $this->salePaymentService->addPayment($this->selectedSale, $this->getPaymentFormData());

            $success = $result['success'] ?? false;

            $message = $result['message'] ?? ($success
                ? 'Pago agregado exitosamente'
                : 'Error al agregar el pago');
            $type = $success ? 'success' : ($result['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'sales',
            ]);

            if ($success) {
                $this->showAddPaymentModal = false;
                $this->dispatch('refresh-sales-table');
                $this->showAddPaymentModal = false;
                return;
            }

            if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new MessageBag($result['validation-errors'] ?? []));
                return;
            }
            
            return;
        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Ocurrió un error inesperado al agregar el pago: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'sales',
            ]
            );
        }
    }

    private function getPaymentFormData(): array
    {
        return [
            'sale_id' => $this->selectedSale->id,
            'amount' => $this->amount,
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
