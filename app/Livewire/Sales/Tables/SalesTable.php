<?php

namespace App\Livewire\Sales\Tables;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalePayment;
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
    public bool $showPendingAndPartialSales = false;

    // Modal states
    public bool $showCreateSaleModal = false;
    public bool $showUpdateSaleModal = false;
    public bool $showDeleteSaleModal = false;
    public bool $showViewSaleModal = false;
    public bool $showAddPaymentModal = false;
    public bool $showPaymentHistoryModal = false;

    public bool $canCreateNewSale = true; // Flag to control creation of new sales

    // Date filtering
    public $dateFilter = 'all';
    public $startDate = null;
    public $endDate = null;

    // Context variables for filtering
    public $contextCustomerId = null;
    public $contextRouteId = null;

    public ?Sale $selectedSale = null;

    protected $listeners = [
        'refresh-sales-table' => '$refresh',
        'flash-sales-table-message' => 'flashSalesTableMessage',
        'show-sales-table-message' => 'showSalesTableMessage',
    ];

    public function showSalesTableMessage($result)
    {
        $this->flashSalesTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function flashSalesTableMessage(string $message, string $type): void
    {
        session()->flash('message', [
            'header' => 'sales-table',
            'text' => $message,
            'type' => $type,
        ]);
    }

    protected SaleService $saleService;

    public function boot()
    {
        $this->saleService = app(SaleService::class);
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

            default => false,
        };

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

    public function togglePendingAndPartialSales()
    {
        $this->showPendingAndPartialSales = !$this->showPendingAndPartialSales;
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
        $this->dispatch('open-create-sale-modal');
    }

    public function openEditModal($saleId)
    {
        $this->dispatch('open-update-sale-modal', $saleId);
    }

    public function openViewModal($saleId)
    {
        $this->dispatch('open-view-sale-modal', $saleId);
    }

    public function openDeleteModal($saleId)
    {
        $this->dispatch('open-delete-sale-modal', $saleId);
    }

    public function openAddPaymentModal($saleId)
    {
        $this->dispatch('open-add-payment-modal', $saleId);
    }

    public function openPaymentHistoryModal($saleId)
    {
        $this->dispatch('open-payment-history-modal', $saleId);
    }

    public function render()
    {
        $sales = $this->saleService->searchSales(
            $this->search,
            $this->sortField,
            $this->sortDirection,
            $this->perPage,
            $this->includeDeleted,
            $this->contextRouteId,
            $this->contextCustomerId,
            $this->showPendingAndPartialSales,
            $this->startDate,
            $this->endDate,
        );

        // Calculate total amount across filtered dataset (not only current page)
        $totalAmount = $this->saleService->getTotalAmount(
            search: $this->search,
            includeDeleted: false,
            routeId: $this->contextRouteId,
            customerId: $this->contextCustomerId,
            showPendingAndPartialSales: $this->showPendingAndPartialSales,
            startDate: $this->startDate,
            endDate: $this->endDate,
        );

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
