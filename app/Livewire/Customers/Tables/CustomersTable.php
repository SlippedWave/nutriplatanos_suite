<?php

namespace App\Livewire\Customers\Tables;

use App\Models\Customer;
use App\Services\CustomerService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public bool $includeDeleted = false;

    protected $listeners = [
        'customers-info-updated' => '$refresh',
        'show-customers-table-message' => 'showCustomersTableMessage',
        'flash-customers-table-message' => 'flashCustomersTableMessage'
    ];

    protected CustomerService $customerService;

    public function boot(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function showCustomersTableMessage($result)
    {
        $this->flashCustomersTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function flashCustomersTableMessage($message, $type)
    {
        session()->flash('message', [
            'header' => 'customers-table',
            'text' => $message,
            'type' => $type,
        ]);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
        $this->resetPage();
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
        return view('livewire.customers.tables.customers-table', [
            'customers' => $this->customerService->searchCustomers(
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage,
                $this->includeDeleted
            )
        ]);
    }
}
