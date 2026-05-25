<flux:modal wire:model="showUpdateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Actualizar reembolso') }}
        </flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="updateRefund">

            <flux:field>
                <flux:select wire:model.live="refund_method" label="{{ __('Método de reembolso') }}">
                    @foreach ($refund_methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:input wire:model="reason" label="{{ __('Razón del reembolso') }}" required />
            </flux:field>

            <flux:field>
                <x-money-input wire:model="refunded_amount" label="{{ __('Monto reembolsado') }}" required />
            </flux:field>
            
            <div class="flex justify-end mt-4">
                <flux:button wire:click="updateRefund" variant="primary" wire:loading.attr="disabled"
                    wire:target="updateRefund" class="w-full sm:w-auto">
                    <span wire:loading.remove wire:target="updateRefund">{{ __('Actualizar') }}</span>
                    <span wire:loading wire:target="updateRefund">Actualizando...</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
