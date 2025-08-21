<?php

use Livewire\Volt\Component;

new class extends Component {
    
    //
}; ?>

<section class="w-full">
    <x-welcome-section welcome-message="Gestiona la contabilidad del negocio desde aquí." />
        <div class="mt-6 w-full max-w-full overflow-hidden">
        <div class="overflow-x-auto">
                @livewire('sales.tables.sales-table')
        </div>
    </div>
    <div class="mt-6 w-full max-w-full overflow-hidden">
        <div class="overflow-x-auto">
                @livewire('accounting.tables.expenses-table')
        </div>
    </div>
    
</section>

