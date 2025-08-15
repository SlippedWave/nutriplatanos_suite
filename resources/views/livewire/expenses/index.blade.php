<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    <x-welcome-section welcome-message="Gestiona los gastos del negocio desde aquí." />
    <div class="mt-6 w-full max-w-full overflow-hidden">
        <div class="overflow-x-auto">
            @livewire('expenses.tables.expenses-table')
        </div>
    </div>
</section>

