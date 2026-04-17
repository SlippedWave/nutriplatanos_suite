<div class="d-flex justify-content-center">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 d-flex flex-column align-items-center text-center">
        @if($refund)
            <div class="d-flex justify-content-center w-100">
                    <flux:button variant="danger" wire:click="$dispatch('open-delete-refund-modal', { refund_id: {{$refund->id}} })" class="me-2">
                        {{ __('Borrar reembolso') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="$dispatch('open-update-refund-modal', { refund_id: {{ $refund->id }} })">
                        {{ __('Editar reembolso') }}
                    </flux:button>
            </div>
        @else
            <p class="text-blue-700 mb-3">{{ __('No hay reembolsos asociados a esta venta.') }}</p>
            <flux:button variant="primary" wire:click="$dispatch('open-create-refund-modal', { sale_id: {{ $sale_id }} })">
                {{ __('Crear reembolso') }}
            </flux:button>
        @endif
    </div>
</div>
