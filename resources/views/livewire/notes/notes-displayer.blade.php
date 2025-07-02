<div class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notas:</h3>
            <flux:button 
                variant="primary" 
                class="bg-secondary-400! hover:bg-secondary-300!"
                icon="plus"
                wire:click="$emit('openModal', 'routes.add-note-modal', ['route' => $route])">
                Añadir nota
            </flux:button>
        </div>
        <ul class="list-disc pl-5 space-y-2">
            @forelse ($notes as $note)
                <li class="text-sm text-gray-700">
                    <span class="font-semibold">{{ $note->created_at->format('d/m/Y H:i') }}:</span> {{ $note->content }}
                </li>
            @empty
                <li class="text-sm text-gray-500">No hay notas registradas para esta ruta.</li>
            @endforelse
        </ul>
        
    </div>