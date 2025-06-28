<?php

namespace App\Livewire\Sells\Tables;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SellsTable extends Component
{
    use WithPagination;

    public $customer_id = null;
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $dateFilter = 'all'; // all, today, week, month
    public $startDate = null;
    public $endDate = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
        'dateFilter' => ['except' => 'all'],
    ];

    public function mount($customer_id = null)
    {
        $this->customer_id = $customer_id;
        $this->applyDateFilter();
    }

    public function updatedSearch()
    {
        $this->resetPage();
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

    public function render()
    {
        $salesQuery = Sale::with(['customer', 'user'])
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
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

        return view('livewire.sells.tables.sells-table', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
        ]);
    }
}
