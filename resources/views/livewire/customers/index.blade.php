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
</section>

