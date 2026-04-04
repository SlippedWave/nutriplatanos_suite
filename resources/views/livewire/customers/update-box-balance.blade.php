<flux:modal wire:model="showUpdateModal" class="space-y-6 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-md md:max-w-lg lg:max-w-2xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
        <flux:heading size="lg" class="text-primary-800!">{{ __('Actualizar Saldo de Caja') }}</flux:heading>
    </div>

    @if($customer)
        <div class="space-y-4">
            <div class="bg-primary-100 p-4 rounded-md">
                <h4 class="font-semibold">{{ __('Saldo de Caja Actual') }}</h4>
                <p>{{ $customer->getBoxBalance() }}</p>
            </div>
                <div class="bg-primary-100 p-4 rounded-md">
                    <h4 class="font-semibold">{{ __('Actualizar Saldo de Caja') }}</h4>
                    <div class="flex flex-col space-y-2">
                       <flux:field>
                            <flux:input 
                                wire:model="box_balance_delivered" 
                                label="{{ __('Cajas dejadas') }}" 
                                type="text"
                                placeholder=""
                                class="text-[var(--color-text)]!"
                            />
                            <flux:error name="box_balance_delivered" />
                        </flux:field>

                        <flux:field>
                            <flux:input 
                                wire:model="box_balance_returned" 
                                label="{{ __('Cajas recogidas') }}" 
                                type="text"
                                placeholder=""
                                class="text-[var(--color-text)]!"
                            />
                            <flux:error name="box_balance_returned" />
                        </flux:field>
                    </div>
                </div>
                <div class="flex justify-end">
                    <flux:button 
                        wire:click="updateBoxBalance" 
                        variant="primary" 
                        wire:loading.attr="disabled"
                        wire:target="updateBoxBalance"
                        class="w-full sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="updateBoxBalance">{{ __('Actualizar') }}</span>
                        <span wire:loading wire:target="updateBoxBalance">Actualizando...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    @endif
</flux:modal>

    