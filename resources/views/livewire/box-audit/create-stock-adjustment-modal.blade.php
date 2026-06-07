<flux:modal wire:model="showModal" class="space-y-4 border-0 bg-background!">
    <flux:heading size="lg">{{ __('Ajuste Manual de Stock') }}</flux:heading>

    <form wire:submit.prevent="save" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('Cámara') }}</flux:label>
            <flux:select wire:model="camera_id" required>
                <flux:select.option value="">{{ __('Seleccionar cámara...') }}</flux:select.option>
                @foreach($cameras as $camera)
                    <flux:select.option value="{{ $camera->id }}">
                        {{ $camera->name }} — {{ __('stock actual') }}: {{ $camera->getCurrentStock() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="camera_id" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>{{ __('Dirección') }}</flux:label>
                <flux:select wire:model="direction">
                    <flux:select.option value="in">Entrada (+)</flux:select.option>
                    <flux:select.option value="out">Salida (−)</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:input
                    wire:model="quantity"
                    label="{{ __('Cantidad') }}"
                    type="number"
                    min="1"
                    placeholder="0"
                    class="text-[var(--color-text)]!"
                    required
                />
                <flux:error name="quantity" />
            </flux:field>
        </div>

        <flux:field>
            <flux:textarea
                wire:model="reason"
                label="{{ __('Motivo') }}"
                placeholder="{{ __('Describe el motivo del ajuste...') }}"
                rows="2"
                class="text-[var(--color-text)]!"
            />
        </flux:field>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('Guardar Ajuste') }}</span>
                <span wire:loading wire:target="save">{{ __('Guardando...') }}</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
