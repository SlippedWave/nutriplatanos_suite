<?php

namespace App\Livewire\Sales\Tables;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use App\Models\Route;
use App\Services\SaleService;
use App\Services\SalePaymentService;

use Livewire\Component;
use Livewire\WithPagination;

class SalesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $includeDeleted = false;

    // Modal states
    public bool $showCreateSaleModal = false;
    public bool $showUpdateSaleModal = false;
    public bool $showDeleteSaleModal = false;
    public bool $showViewSaleModal = false;
    public bool $showAddPaymentModal = false;
    public bool $showPaymentHistoryModal = false;

    // Form fields
    public $customer_id = '';
    public $route_id = '';
    public $payment_status = 'pending';
    public $paid_amount = 0.00; // New field for paid amount
    public $notes = '';
    public $saleProducts = [];

    // Payment form fields
    public $payment_amount = 0.00;
    public $payment_date = '';
    public $payment_method = 'cash';
    public $payment_route_id = '';
    public $payment_notes = '';

    public bool $canCreateNewSale = true; // Flag to control creation of new sales

    // Date filtering
    public $dateFilter = 'all';
    public $startDate = null;
    public $endDate = null;

    // Context variables for filtering
    public $contextCustomerId = null;
    public $contextRouteId = null;

    public ?Sale $selectedSale = null;

    protected SaleService $saleService;
    protected SalePaymentService $salePaymentService;

    public function boot(SaleService $saleService, SalePaymentService $salePaymentService)
    {
        $this->saleService = $saleService;
        $this->salePaymentService = $salePaymentService;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'dateFilter' => ['except' => 'all'],
        'includeDeleted' => ['except' => false],
    ];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'route_id' => 'required|exists:routes,id',
        'payment_status' => 'required|in:pending,paid,partial,cancelled',
        'paid_amount' => 'nullable|numeric|min:0|max:999999.99',
        'notes' => 'nullable|string|max:1000',
        'saleProducts.*.product_id' => 'required|exists:products,id',
        'saleProducts.*.quantity' => 'required|numeric|min:0.001|max:999999.999',
        'saleProducts.*.price_per_unit' => 'required|numeric|min:0.01|max:999999.99',
        // Payment validation rules
        'payment_amount' => 'required|numeric|min:0.01|max:999999.99',
        'payment_date' => 'required|date|before_or_equal:today',
        'payment_method' => 'required|in:cash,transfer,check,card,other',
        'payment_route_id' => 'nullable|exists:routes,id',
        'payment_notes' => 'nullable|string|max:1000',

    ];

    protected $messages = [
        'customer_id.required' => 'El cliente es obligatorio.',
        'customer_id.exists' => 'El cliente seleccionado no es válido.',
        'route_id.required' => 'La ruta es obligatoria.',
        'route_id.exists' => 'La ruta seleccionada no es válida.',
        'payment_status.required' => 'El estado de pago es obligatorio.',
        'payment_status.in' => 'El estado de pago seleccionado no es válido.',
        'paid_amount.numeric' => 'El monto pagado debe ser un número.',
        'paid_amount.min' => 'El monto pagado no puede ser negativo.',
        'paid_amount.max' => 'El monto pagado es demasiado alto.',
        'saleProducts.*.product_id.required' => 'Debe seleccionar un producto.',
        'saleProducts.*.product_id.exists' => 'El producto seleccionado no es válido.',
        'saleProducts.*.quantity.required' => 'La cantidad es obligatoria.',
        'saleProducts.*.quantity.numeric' => 'La cantidad debe ser un número.',
        'saleProducts.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
        'saleProducts.*.price_per_unit.required' => 'El precio unitario es obligatorio.',
        'saleProducts.*.price_per_unit.numeric' => 'El precio unitario debe ser un número.',
        'saleProducts.*.price_per_unit.min' => 'El precio unitario debe ser mayor que 0.',
        // Payment validation messages
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

    public function mount($customer_id = null, $route_id = null)
    {
        $this->contextCustomerId = $customer_id;
        $this->contextRouteId = $route_id;
        $this->customer_id = $customer_id ?? '';
        $this->route_id = $route_id ?? '';

        // Determine sale creation eligibility based on contextual parameters
        $this->canCreateNewSale = match (true) {
            // For customer context: verify customer exists and maintains active status
            !empty($this->contextCustomerId) => Customer::where('id', $this->contextCustomerId)
                ->where('active', true)
                ->exists(),

            // For route context: verify route exists with active operational status
            !empty($this->contextRouteId) => Route::where('id', $this->contextRouteId)
                ->where('status', 'active')
                ->exists(),

            // Default: require explicit context association for new sale creation
            default => false,
        };

        // Initialize products array with one empty product
        $this->addProduct();

        $this->applyDateFilter();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->applyDateFilter();
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
        $this->resetPage();
    }

    private function applyDateFilter()
    {
        $now = now();

        switch ($this->dateFilter) {
            case 'today':
                $this->startDate = $now->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                break;
            case 'week':
                $this->startDate = $now->startOfWeek()->toDateString();
                $this->endDate = $now->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->toDateString();
                $this->endDate = $now->endOfMonth()->toDateString();
                break;
            default:
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // Modal management methods
    public function openCreateModal()
    {
        $this->resetFormFields();
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showCreateSaleModal = true;
    }

    public function openEditModal($saleId)
    {
        $this->selectedSale = Sale::with(['saleDetails.product', 'customer', 'route'])->findOrFail($saleId);
        $this->fillForm($this->selectedSale);
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showUpdateSaleModal = true;
    }

    public function openViewModal($saleId)
    {
        $this->selectedSale = Sale::with(['saleDetails.product', 'customer', 'route', 'user'])
            ->withTrashed()
            ->findOrFail($saleId);
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showViewSaleModal = true;
    }

    public function openDeleteModal($saleId)
    {
        $this->selectedSale = Sale::findOrFail($saleId);
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showDeleteSaleModal = true;
    }

    public function openAddPaymentModal($saleId)
    {
        $this->selectedSale = Sale::with(['saleDetails', 'payments'])->findOrFail($saleId);
        $this->resetPaymentFields();
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showAddPaymentModal = true;
    }

    public function openPaymentHistoryModal($saleId)
    {
        $this->selectedSale = Sale::with(['payments.user', 'payments.route', 'saleDetails'])->findOrFail($saleId);
        $this->resetValidation(); // Clear any previous validation errors
        session()->forget(['error', 'message']); // Clear any session messages
        $this->showPaymentHistoryModal = true;
    }

    public function closeModals()
    {
        $this->showCreateSaleModal = false;
        $this->showUpdateSaleModal = false;
        $this->showDeleteSaleModal = false;
        $this->showViewSaleModal = false;
        $this->showAddPaymentModal = false;
        $this->showPaymentHistoryModal = false;
        $this->selectedSale = null;
        $this->resetFormFields();
        $this->resetPaymentFields();
        $this->resetValidation(); // Clear validation errors when closing modals

        // Clear session flash messages
        session()->forget(['error', 'message']);
    }

    public function clearErrors()
    {
        $this->resetValidation();
        session()->forget(['error', 'message']);
    }

    public function clearErrorsForModal($modalType = null)
    {
        // Clear validation errors
        $this->resetValidation();

        // Clear session errors only if the current modal is open
        $shouldClearSession = match ($modalType) {
            'create' => $this->showCreateSaleModal,
            'update' => $this->showUpdateSaleModal,
            'delete' => $this->showDeleteSaleModal,
            'view' => $this->showViewSaleModal,
            'payment' => $this->showAddPaymentModal,
            'payment_history' => $this->showPaymentHistoryModal,
            default => true, // Clear if no specific modal type provided
        };

        if ($shouldClearSession) {
            session()->forget(['error', 'message']);
        }
    }

    // Product management methods
    public function addProduct()
    {
        $this->saleProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'price_per_unit' => 0,
        ];
    }

    public function removeProduct($index)
    {
        if (count($this->saleProducts) > 1) {
            unset($this->saleProducts[$index]);
            $this->saleProducts = array_values($this->saleProducts); // Re-index array
        }
    }    // CRUD operations using SaleService
    public function createSale()
    {
        try {
            // Validate form data first
            $this->validate();

            $result = $this->saleService->createSale($this->getFormData());

            if ($result['success']) {
                $this->closeModals();
                session()->flash('message', $result['message']);
                $this->resetPage();
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
                        session()->flash('error', $result['message']);
                        break;

                    case 'authorization':
                        // Close modal for authorization errors
                        $this->closeModals();
                        session()->flash('error', $result['message']);
                        break;

                    default:
                        // Don't close modal for other errors, let user retry
                        session()->flash('error', $result['message']);
                        break;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Livewire validation failed, don't close modal
            session()->flash('error', 'Por favor, corrige los errores antes de continuar.');
        }
    }

    public function updateSale()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta.');
            return;
        }

        try {
            // Validate form data first
            $this->validate();

            $result = $this->saleService->updateSale($this->selectedSale, $this->getFormData());

            if ($result['success']) {
                $this->closeModals();
                session()->flash('message', $result['message']);
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
                        session()->flash('error', $result['message']);
                        break;

                    case 'authorization':
                        // Close modal for authorization errors
                        $this->closeModals();
                        session()->flash('error', $result['message']);
                        break;

                    default:
                        // Don't close modal for other errors, let user retry
                        session()->flash('error', $result['message']);
                        break;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Livewire validation failed, don't close modal
            session()->flash('error', 'Por favor, corrige los errores antes de continuar.');
        }
    }

    public function deleteSale()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta.');
            return;
        }

        $result = $this->saleService->deleteSale($this->selectedSale);

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            // Handle different types of errors
            switch ($result['type'] ?? 'error') {
                case 'authorization':
                    // Close modal for authorization errors
                    $this->closeModals();
                    session()->flash('error', $result['message']);
                    break;

                default:
                    // Don't close modal for other errors, let user retry
                    session()->flash('error', $result['message']);
                    break;
            }
        }
    }

    // Payment management methods
    public function addPayment()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta.');
            return;
        }

        try {
            // Validate payment data
            $this->validate([
                'payment_amount' => 'required|numeric|min:0.01|max:999999.99',
                'payment_date' => 'required|date|before_or_equal:today',
                'payment_method' => 'required|in:cash,transfer,check,card,other',
                'payment_route_id' => 'nullable|exists:routes,id',
                'payment_notes' => 'nullable|string|max:1000',
            ]);

            $result = $this->salePaymentService->addPayment($this->selectedSale, $this->getPaymentFormData());

            if ($result['success']) {
                $this->closeModals();
                session()->flash('message', $result['message']);
                $this->resetPage();
            } else {
                // Handle different types of errors
                switch ($result['type'] ?? 'error') {
                    case 'validation':
                        if (isset($result['errors'])) {
                            foreach ($result['errors'] as $field => $messages) {
                                $this->addError($field, implode(' ', $messages));
                            }
                        }
                        session()->flash('error', $result['message']);
                        break;

                    case 'authorization':
                        $this->closeModals();
                        session()->flash('error', $result['message']);
                        break;

                    default:
                        session()->flash('error', $result['message']);
                        break;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Por favor, corrige los errores antes de continuar.');
        }
    }

    public function markAsFullyPaid($saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $result = $this->salePaymentService->markAsFullyPaid($sale);

        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    // Utility methods
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
            'paid_amount' => match ($this->payment_status) {
                'partial' => $this->paid_amount,
                'paid' => $totalAmount,
                default => null,
            },
            'notes' => $this->notes,
            'products' => array_values($validProducts), // Re-index
        ];
    }

    private function resetFormFields()
    {
        $this->customer_id = $this->contextCustomerId ?? '';
        $this->route_id = $this->contextRouteId ?? '';
        $this->payment_status = 'pending';
        $this->paid_amount = 0.00;
        $this->notes = '';
        $this->saleProducts = [];
        $this->addProduct(); // Add one empty product
    }

    private function resetPaymentFields()
    {
        $this->payment_amount = 0.00;
        $this->payment_date = now()->toDateString();
        $this->payment_method = 'cash';
        $this->payment_route_id = $this->contextRouteId ?? '';
        $this->payment_notes = '';
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

    private function fillForm(Sale $sale)
    {
        $this->customer_id = $sale->customer_id;
        $this->route_id = $sale->route_id;
        $this->payment_status = $sale->payment_status;
        $this->paid_amount = $sale->paid_amount ?? 0.00;
        $this->notes = '';

        // Fill products
        $this->saleProducts = $sale->saleDetails->map(function ($detail) {
            return [
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'price_per_unit' => $detail->price_per_unit,
            ];
        })->toArray();

        if (empty($this->saleProducts)) {
            $this->addProduct();
        }
    }

    public function hasOpenModal(): bool
    {
        return $this->showCreateSaleModal ||
            $this->showUpdateSaleModal ||
            $this->showDeleteSaleModal ||
            $this->showViewSaleModal ||
            $this->showAddPaymentModal ||
            $this->showPaymentHistoryModal;
    }

    public function render()
    {
        $filters = [];

        if ($this->contextCustomerId) {
            $filters['customer_id'] = $this->contextCustomerId;
        }

        if ($this->contextRouteId) {
            $filters['route_id'] = $this->contextRouteId;
        }

        if ($this->startDate && $this->endDate) {
            $filters['start_date'] = $this->startDate;
            $filters['end_date'] = $this->endDate;
        }

        $sales = $this->saleService->searchSales(
            $this->search,
            $this->sortField,
            $this->sortDirection,
            $this->perPage,
            $this->includeDeleted,
            $this->contextRouteId,
            $this->contextCustomerId
        );

        // Calculate total amount for current filtered results
        $totalAmount = $sales->getCollection()->sum(function ($sale) {
            return $sale->saleDetails->sum('total_price');
        });

        return view('livewire.sales.tables.sales-table', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'products' => Product::all(),
            'customers' => Customer::where('active', true)->get(),
            'routes' => Route::where('status', 'active')->get(),
            'paymentMethods' => SalePayment::PAYMENT_METHODS,
        ]);
    }
}
