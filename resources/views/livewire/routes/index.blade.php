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
    <!-- Listen for CreateRouteModal errors and show a transient banner -->
    <div x-data="{ show: false, msg: '', type: 'error' }"
         @route-create-failed.window="show = true; msg = $event.detail?.message || '{{ __('Ocurrió un error al crear la ruta.') }}'; setTimeout(() => show = false, 5000)">
        <template x-if="show">
            <div class="mb-3 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg flex justify-between items-center">
                <div x-text="msg"></div>
                <button type="button" @click="show = false" class="text-danger-500 hover:text-danger-700">
                    <span class="sr-only">Close</span>
                    <flux:icon.x-mark class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 4000)" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex justify-between items-center">
            <div>{{ session('message') }}</div>
            <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 4000)" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg flex justify-between items-center">
            <div>{{ session('error') }}</div>
            <button type="button" @click="show = false" class="text-danger-500 hover:text-danger-700">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <x-layouts.routes.layout :heading="$this->heading" :subheading="$this->subheading">
        <div class="mt-5 w-full max-w-full">
            <div class="flex flex-col items-center">
                <div class="flex flex-col items-center w-full">
                    <livewire:routes.create-route-modal />
                </div>
            </div>
        </div>
    </x-layouts.routes.layout>
</section>