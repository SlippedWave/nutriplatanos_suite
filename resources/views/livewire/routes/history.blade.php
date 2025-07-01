<?php

use Livewire\Volt\Component;

new class extends Component {
    public $role;
    public $subheading; 
    public function mount()
    {
        $this->role = auth()->user()->role ?? 'guest';
        $this->subheading = ($this->role === 'admin' || $this->role === 'coordinator') 
            ? __('Aquí puedes ver las rutas que se han registrado en el sistema.') 
            : __('Consulta las rutas que has realizado.');
            
    }
    
};
?>

<section class="w-full">
    
    <x-layouts.routes.layout :heading="__('Rutas')" :subheading="$subheading">
        <div class="mt-5 w-full max-w-full">
            @livewire('routes.tables.routes-table', ['user_id' => ($role == 'admin' || $role=='coordinator') ? null : auth()->user()->id ])
        </div>
    </x-layouts.routes.layout>
</section>