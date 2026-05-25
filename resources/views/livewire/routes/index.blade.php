<?php

use Livewire\Volt\Component;
use App\Models\Route;

new class extends Component {
    public $role;
    public $user;
    public $activeRoute = null;

    public $heading = '';
    public $subheading = '';

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->role ?? 'guest';
        $this->activeRoute = $this->user->routes->where('status', 'active')->first();

        if ($this->activeRoute) {
            $this->heading = 'Ruta Activa: ' . $this->activeRoute->title;
            $this->subheading = 'Gestionando ruta del ' . $this->activeRoute->created_at->format('d M Y');
            return $this->redirectRoute('routes.show', ['route' => $this->activeRoute->id]);
        } else {
            $this->heading = 'Crear Nueva Ruta';
            $this->subheading = 'Puedes crear una nueva ruta para comenzar a gestionar tus entregas.';
        }
    }
};
?>
<section class="w-full">
    <x-layouts.routes.layout :heading="$this->heading" :subheading="$this->subheading">
        <div class="mt-5 w-full max-w-full">
            <div class="flex flex-col items-center">
                <div class="flex flex-col items-center w-full">
                    @if ($activeRoute)
                        <p class="text-lg text-gray-700 mb-4">Actualmente tienes una ruta activa: <strong>{{ $activeRoute->title }}</strong>.</p>
                        <p class="text-gray-600 mb-6">Puedes gestionar esta ruta o cerrarla para crear una nueva.</p>
                    @else
                        <livewire:routes.create-route-modal />
                    @endif
                </div>
            </div>
        </div>
    </x-layouts.routes.layout>
</section>