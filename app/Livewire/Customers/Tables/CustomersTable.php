<?php

namespace App\Livewire\Customers\Tables;

use App\Models\Customer;
use App\Models\Note;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Mockery\Matcher\Not;

class CustomersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

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

    public ?Customer $selectedCustomer = null;

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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->selectedCustomer = null;
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
        $this->selectedCustomer = Customer::findOrFail($customerId);
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

    public function createCustomer()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = Customer::create($validated);

        // If notes are provided, create a note for the customer
        if (isset($validated['notes']) && $validated['notes']) {
            Note::create([
                'user_id' => auth()->user()->id,
                'content' => $validated['notes'],
                'type' => 'customer',
                'notable_type' => Customer::class,
                'notable_id' => $customer->id,
            ]);

            $this->dispatch('note-created');
        }

        $this->closeModals();
        $this->dispatch('customer-created');
        session()->flash('message', 'Cliente creado correctamente.');
    }

    public function updateCustomer()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($this->customer->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'active' => 'boolean',
        ]);

        $this->customer->update($validated);

        $this->closeModals();
        $this->dispatch('customer-updated');
        session()->flash('message', 'Cliente actualizado correctamente.');
    }

    public function deleteCustomer()
    {
        $this->customer->delete();

        $this->closeModals();
        $this->dispatch('customer-deleted');
        session()->flash('message', 'Cliente eliminado correctamente.');
    }

    public function resetForm()
    {
        $this->resetFormFields();
        $this->selectedCustomer = null;
    }

    public function resetFormFields()
    {
        $this->reset(['name', 'email', 'phone', 'address', 'rfc', 'active']);
    }

    public function fillForm(Customer $customer)
    {
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->rfc = $customer->rfc;
        $this->active = $customer->active;
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%')
                    ->orWhere('rfc', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.customers.tables.customers-table', [
            'customers' => $customers,
        ]);
    }
}
