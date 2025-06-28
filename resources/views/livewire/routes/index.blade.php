<?php

use Livewire\Volt\Component;

new class extends Component {
    public function mount()
    {
        $this->role = auth()->user()->role ?? 'guest';
    }
    
    public $role;
};
?>

<section class="w-full">
    
    <x-layouts.routes.layout :heading="__('Rutas')" :subheading="__('Aquí puedes gestionar las rutas de tu negocio.')" :role="$role">
        <div class="mt-5 w-full max-w-full">
{{--             @livewire('routes.tables.routes-table')
 --}}        
        </div>
    </x-layouts.routes.layout>
</section>