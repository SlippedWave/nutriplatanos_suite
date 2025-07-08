<?php

namespace App\Livewire\Sales\Tables;

use App\Models\Sale;
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

    public $context = 'sales'; // Context for the table, can be used for different views
    public bool $showCreateSaleModal = false;
    public bool $showDeleteSaleModal = false;
    public bool $showViewSaleModal = false;
    public bool $showEditSaleModal = false;

    public ?Sale $selectedSale = null;

    protected SaleService $saleService;

    public function boot(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public $route_id = null;

    public $customer_id = null;
    public $dateFilter = 'all'; // all, today, week, month
    public $startDate = null;
    public $endDate = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'dateFilter' => ['except' => 'all'],
        'customerFilter' => ['except' => null],
        'routeFilter' => ['except' => null],
        'carrierFilter' => ['except' => null],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSortField()
    {
        $this->resetPage();
    }


    public function mount($customer_id = null, $route_id = null, $context = 'sales')
    {
        $this->customer_id = $customer_id;
        $this->route_id = $route_id;
        $this->context = $context;

        // Set default date filter
        $this->dateFilter = 'all';
        $this->applyDateFilter();
    }


    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->applyDateFilter();
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

    public function createSale()
    {
        $result = $this->saleService->createSale($this->getFormData());

        if ($result['success']) {
            session()->flash('message', 'Venta creada exitosamente!');
            $this->showCreateSaleModal = false;
            $this->resetFormFields();
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function updateSale()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta para editar.');
            return;
        }

        $result = $this->saleService->updateSale($this->selectedSale->id, $this->getFormData());

        if ($result['success']) {
            session()->flash('message', 'Venta actualizada exitosamente!');
            $this->showEditSaleModal = false;
            $this->resetFormFields();
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function deleteSale()
    {
        if (!$this->selectedSale) {
            session()->flash('error', 'No se ha seleccionado ninguna venta para eliminar.');
            return;
        }

        $result = $this->saleService->deleteSale($this->selectedSale->id);

        if ($result['success']) {
            session()->flash('message', 'Venta eliminada exitosamente!');
            $this->showDeleteSaleModal = false;
            $this->resetFormFields();
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    private function getFormData()
    {
        return [
            'customer_id' => $this->customer_id,
            'route_id' => $this->route_id,
            'total_amount' => $this->selectedSale ? $this->selectedSale->total_amount : 0,
            'weight_kg' => $this->selectedSale ? $this->selectedSale->weight_kg : 0,
            'price_per_kg' => $this->selectedSale ? $this->selectedSale->price_per_kg : 0,
            'user_id' => auth()->id(),
        ];
    }



    public function render()
    {
        $salesQuery = Sale::with(['customer', 'user'])
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
            })
            ->when($this->route_id, function ($query) {
                $query->where('route_id', $this->route_id);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('total_amount', 'like', '%' . $this->search . '%')
                    ->orWhere('weight_kg', 'like', '%' . $this->search . '%')
                    ->orWhere('price_per_kg', 'like', '%' . $this->search . '%');
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('created_at', [
                    $this->startDate . ' 00:00:00',
                    $this->endDate . ' 23:59:59'
                ]);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $sales = $salesQuery->paginate($this->perPage);

        // Calculate total amount for current filtered results
        $totalAmount = $salesQuery
            ->get()
            ->sum('total_amount');

        return view('livewire.sales.tables.sales-table', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
        ]);
    }
}
