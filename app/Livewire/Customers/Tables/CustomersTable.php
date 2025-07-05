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

    // Modal states
    public bool $showCreateModal = false;
    public bool $showUpdateModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;

    // Form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $rfc = '';
    public $active = true;
    public $notes = '';

    public ?Customer $selectedCustomer = null;

    protected CustomerService $customerService;

    public function boot(CustomerService $customerService)
    {
        $this->customerService = $customerService;
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

    // Modal management methods
    public function openCreateModal()
    {
        $this->resetFormFields();
        $this->showCreateModal = true;
    }

    public function openEditModal($customerId)
    {
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->fillForm($this->selectedCustomer);
        $this->showUpdateModal = true;
    }

    public function openViewModal($customerId)
    {
        $this->selectedCustomer = Customer::withTrashed()->findOrFail($customerId);
        $this->showViewModal = true;
    }

    public function openDeleteModal($customerId)
    {
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showUpdateModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->selectedCustomer = null;
        $this->resetFormFields();
    }

    // CRUD operations using CustomerService
    public function createCustomer()
    {
        $result = $this->customerService->createCustomer($this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
            $this->resetPage();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function updateCustomer()
    {
        if (!$this->selectedCustomer) {
            session()->flash('error', 'No se ha seleccionado ningún cliente.');
            return;
        }

        $result = $this->customerService->updateCustomer($this->selectedCustomer, $this->getFormData());

        if ($result['success']) {
            $this->closeModals();
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function deleteCustomer()
    {
        if (!$this->selectedCustomer) {
            session()->flash('error', 'No se ha seleccionado ningún cliente.');
            return;
        }

        $result = $this->customerService->deleteCustomer($this->selectedCustomer);

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
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'rfc' => $this->rfc,
            'active' => $this->active,
            'notes' => $this->notes,
        ];
    }

    private function resetFormFields()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->rfc = '';
        $this->active = true;
        $this->notes = '';
    }

    private function fillForm(Customer $customer)
    {
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->rfc = $customer->rfc ?? '';
        $this->active = $customer->active;
        $this->notes = '';
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
