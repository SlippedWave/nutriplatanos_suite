<?php

use Livewire\Volt\Component;
use App\Models\Route;

new class extends Component {
    public $role;
    public $user;
    public $activeRoute = null;
    public $heading = '';
    public $subheading = '';

    public bool $showCreateModal = false;
    public $title = '';
    public $notes = '';

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->role ?? 'guest';
        $this->activeRoute = $this->user->routes->where('status', 'active')->first();



        $this->title = 'Ruta del día ' . now()->format('d M Y');

        if ($this->activeRoute) {
            $this->heading = 'Ruta Activa: ' . $this->activeRoute->title;
            $this->subheading = 'Gestionando ruta del ' . $this->activeRoute->created_at->format('d M Y');
            return $this->redirectRoute('routes.show', ['route' => $this->activeRoute->id]);
        } 
        else {
            $this->heading = 'Crear Nueva Ruta';
            $this->subheading = 'Puedes crear una nueva ruta para comenzar a gestionar tus entregas.';
        }
    }

    public function createRoute()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $route = Route::create([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'carrier_id' => $this->user->id,
            'status' => 'active',
        ]);

        $routeId = $route->id; 

        if ($validated['notes']) {
            Note::create([
                'user_id' => $this->user->id,
                'content' => $validated['notes'],
                'type' => 'route',
                'notable_type' => Route::class,
                'notable_id' => $routeId,
            ]);

            $this->dispatch('noteCreated', [
                'noteId' => $routeId,
                'notableType' => Route::class,
                'notableId' => $routeId,
            ]);
        }
        
        $this->reset(['title', 'notes']);
        $this->showCreateModal = false;
        $this->mount();        
        $this->dispatch('routeCreated', ['routeId' => $routeId]);
        session()->flash('message', 'Ruta creada exitosamente!');
    }

    public function toggleCreateModal()
    {
        $this->showCreateModal = !$this->showCreateModal;
        if (!$this->showCreateModal) {
            $this->reset(['title', 'notes']);
        }
    }
};
?>

<section class="w-full">
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
                    <flux:button wire:click="toggleCreateModal" variant="primary" icon="plus" class="max-w-[220px]">
                        {{ __('Crear nueva ruta') }}
                    </flux:button>

                    <!-- Modal para crear ruta -->
                    <flux:modal wire:model="showCreateModal" class="space-y-6 border-0 bg-background!">
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">{{ __('Crear Nueva Ruta') }}</flux:heading>
                        </div>
                                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" />
                        <form wire:submit="createRoute" class="space-y-4">
                            <flux:field>
                                <flux:input wire:model="title" label="{{ __('Título de la ruta') }}" required class="text-[var(--color-text)]!" value="{{"Ruta del día" . now()}}"/>
                                <flux:error name="title" />
                            </flux:field>

                            <flux:field>
                                <flux:textarea wire:model="notes" label="{{ __('Notas adicionales') }}" class="text-[var(--color-text)]!" />
                                <flux:error name="notes" />
                            </flux:field>

                            <div class="flex justify-end gap-3 pt-4">
                                <flux:button variant="ghost" wire:click="$set('showCreateModal', false)">{{ __('Cancelar') }}</flux:button>
                                <flux:button type="submit" variant="primary">{{ __('Crear Ruta') }}</flux:button>
                            </div>
                        </form>
                    </flux:modal>
                </div>
            </div>
        </div>
    </x-layouts.routes.layout>
</section>