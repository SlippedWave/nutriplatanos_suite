<div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    @if($refund)
        <flux:button.group>
            <flux:button variant="danger" disabled>
                {{ __('Borrar reembolso') }}
            </flux:button>
            <flux:button variant="primary" wire:click="$dispatch('open-update-refund-modal', {{ $refund->id }})">
                {{ __('Editar reembolso') }}
            </flux:button>
        </flux:button.group>
    @else
        <p class="text-blue-700">{{ __('No hay reembolsos asociados a esta venta.') }}</p>
        <flux:button variant="primary" wire:click="$dispatch('open-create-refund-modal', { sale_id: {{ $sale_id }} })" class="mt-4">
            {{ __('Crear reembolso') }}
        </flux:button>
    @endif
</div>
