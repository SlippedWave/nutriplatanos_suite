<?php

namespace App\Livewire\Sales\Tables;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Route;
use App\Services\SaleService;
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
    public bool $showCreateModal = false;
    public bool $showUpdateModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;

    // Form fields
    public $customer_id = '';
    public $route_id = '';
    public $payment_status = 'pending';
    public $notes = '';
    public $saleProducts = [];

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

    public function boot(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'dateFilter' => ['except' => 'all'],
        'includeDeleted' => ['except' => false],
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
        $this->showCreateModal = true;
    }

    public function openEditModal($saleId)
    {
        $this->selectedSale = Sale::with(['saleDetails.product', 'customer', 'route'])->findOrFail($saleId);
        $this->fillForm($this->selectedSale);
        $this->showUpdateModal = true;
    }

    public function openViewModal($saleId)
    {
        $this->selectedSale = Sale::with(['saleDetails.product', 'customer', 'route', 'user'])
            ->withTrashed()
            ->findOrFail($saleId);
        $this->showViewModal = true;
    }

    public function openDeleteModal($saleId)
    {
        $this->selectedSale = Sale::findOrFail($saleId);
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showUpdateModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->selectedSale = null;
        $this->resetFormFields();
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
    }

    // CRUD operations using SaleService
    public function createSale()
    {
        $result = $this->saleService->createSale($this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function updateSale()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta.');
            return;
        }

        $result = $this->saleService->updateSale($this->selectedSale, $this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
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

        return [
            'customer_id' => $this->customer_id,
            'route_id' => $this->route_id,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'products' => array_values($validProducts), // Re-index
        ];
    }

    private function resetFormFields()
    {
        $this->customer_id = $this->contextCustomerId ?? '';
        $this->route_id = $this->contextRouteId ?? '';
        $this->payment_status = 'pending';
        $this->notes = '';
        $this->saleProducts = [];
        $this->addProduct(); // Add one empty product
    }

    private function fillForm(Sale $sale)
    {
        $this->customer_id = $sale->customer_id;
        $this->route_id = $sale->route_id;
        $this->payment_status = $sale->payment_status;
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
        ]);
    }
}
