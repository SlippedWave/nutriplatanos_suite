<?php

use Livewire\Volt\Component;
use App\Models\Customer;
use Livewire\Attributes\On;

new class extends Component {
    //
    public $customer;

    #[On('box-balance-uploaded')]
    public function onBoxBalanceUploaded()
    {
        $this->js('window.location.reload()');
    }

    public function mount()
    {
        $customerId = request()->route('customer');
        $this->customer = Customer::withTrashed()->findOrFail($customerId);
    }
}; ?>

<section class="w-full">
    <x-welcome-section welcome-message="Mostrando información de {{$customer->name}}" />
    <div class="mt-2 w-full max-w-full overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Nombre:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
            </div>
            
            @if($customer->email)
            <div>
                <p class="text-sm font-medium text-gray-500">Correo Electrónico:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->email }}</p>
            </div>
            @endif
            
            @if($customer->phone)
            <div>
                <p class="text-sm font-medium text-gray-500">Teléfono:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->phone }}</p>
            </div>
            @endif
            
            @if($customer->address)
            <div>
                <p class="text-sm font-medium text-gray-500">Dirección:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->address }}</p>
            </div>
            @endif
            
            <div>
                <p class="text-sm font-medium text-gray-500">Cliente desde:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('d/m/Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Saldo de Cajas:</p>
                <p class="mt-1 text-sm text-gray-900">{{ $customer->getBoxBalance() }}</p>
                <div class="mt-2">
                    <button
                        type="button"
                        wire:click="$dispatch('open-update-box-balance-modal', { customerId: {{ $customer->id }} })"
                        class="text-sm text-blue-600 hover:underline"
                    >
                        Actualizar Saldo
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewire('notes.notes-displayer', ['notable_type' => Customer::class, 'notable_id' => $customer->id])
    <livewire:customers.update-box-balance />
    
    <!-- Sales History Section -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Ventas</h3>
        @livewire('sales.tables.sales-table', ['customer_id' => $customer->id])
    </div>
</section>

