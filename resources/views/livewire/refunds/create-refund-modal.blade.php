<flux:modal wire:modal="showCreateModal"
    class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="font-semibold text-primary-600!">{{ __('Crear reembolso') }}</flux:heading>
    </div>

    <div class="space-y-4">
        <form wire:submit.prevent="createRefund">

            @if (Auth::user()->role !== 'carrier')
                <flux:field>
                    <flux:select wire:model="customer_id" label="{{ __('Cliente') }}" required>
                        <option value="">Seleccionar usuario...</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="customer_id" />
                </flux:field>
            @endif

            <flux:field>
                <flux:select wire:model.live="refund_method" label="{{ __('Método de reembolso') }}">
                    @foreach ($refund_methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="refund_method" />
            </flux:field>

            <flux:field>
                <flux:input wire:model="reason" label="{{ __('Razón del reembolso') }}" required />
                <flux:error name="reason" />
            </flux:field>

            <flux:field>
                <flux:input wire:model="refunded_amount" label="{{ __('Monto reembolsado') }}" required />
                <flux:error name="refunded_amount" />
            </flux:field>
            <div class="flex justify-end mt-4">
                <flux:button wire:click="createRefund" variant="primary" wire:loading.attr="disabled"
                    wire:target="createRefund" class="w-full sm:w-auto">
                    <span wire:loading.remove wire:target="createRefund">{{ __('Crear') }}</span>
                    <span wire:loading wire:target="createRefund">Creando...</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
