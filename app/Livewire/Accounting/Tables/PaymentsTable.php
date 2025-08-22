<?php

namespace App\Livewire\Accounting\Tables;

use App\Services\SalePaymentService;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentsTable extends Component
{
    use WithPagination;
    public $perPage = 5;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    #[Modelable]
    public $dateFilter = 'all';
    public $startDate;
    public $endDate;

    protected SalePaymentService $salePaymentService;

    public function boot()
    {
        $this->salePaymentService = app(SalePaymentService::class);
    }

    public function mount()
    {
        $this->applyDateFilter();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->applyDateFilter();
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

    public function render()
    {
        $payments = $this->salePaymentService->searchPayments($this->startDate, $this->endDate, $this->sortField, $this->sortDirection, $this->perPage);

        $totalAmount = $payments->sum('amount');
        $this->dispatch('paymentsTotalUpdated', $totalAmount);
        return view('livewire.accounting.tables.payments-table', compact('payments', 'totalAmount'));
    }
}
