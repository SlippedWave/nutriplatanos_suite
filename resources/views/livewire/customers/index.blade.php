<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    <x-welcome-section welcome-message="Gestiona los clientes del negocio desde aquí." />
    <div class="mt-6 w-full max-w-full overflow-hidden">
        <div class="overflow-x-auto">
            <livewire:customers.tables.customers-table />
        </div>
    </div>

    <livewire:customers.create-customer-modal />
    <livewire:customers.update-customer-modal />
    <livewire:customers.view-customer-modal />
    <livewire:customers.delete-customer-modal />
</section>

