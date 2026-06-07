<flux:modal wire:model="showModal" class="space-y-4 border-0 bg-background!">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Ajuste Manual de Cajas') }}</flux:heading>
    </div>

    @if(isset($customer))
        <div class="bg-primary-50 rounded-md p-3 text-sm">
            <span class="font-medium">{{ $customer->name }}</span>
            &mdash;
            {{ __('Saldo actual') }}: <span class="font-semibold">{{ $customer->getBoxBalance() }}</span>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('Cantidad') }}</flux:label>
            <flux:description>{{ __('Positivo para agregar cajas adeudadas, negativo para descontarlas.') }}</flux:description>
            <flux:input
                wire:model="quantity"
                type="number"
                placeholder="0"
                class="text-[var(--color-text)]!"
                required
            />
            <flux:error name="quantity" />
        </flux:field>

        <flux:field>
            <flux:textarea
                wire:model="reason"
                label="{{ __('Motivo') }}"
                placeholder="{{ __('Opcional — describe el motivo del ajuste') }}"
                rows="2"
                class="text-[var(--color-text)]!"
            />
            <flux:error name="reason" />
        </flux:field>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
            <flux:button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">{{ __('Guardar Ajuste') }}</span>
                <span wire:loading wire:target="save">{{ __('Guardando...') }}</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
