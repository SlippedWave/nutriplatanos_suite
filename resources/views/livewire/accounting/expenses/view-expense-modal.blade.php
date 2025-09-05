<flux:modal wire:model="$showViewModal" class="space-y-4 border-0 bg-background! mx-auto w-full max-w-[96vw] sm:max-w-sm md:max-w-md lg:max-w-xl p-3 sm:p-4 rounded-none sm:rounded-xl overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between">
       <flux:heading size="lg" class="text-base sm:text-lg">{{ __('Detalles de la Venta') }}</flux:heading>
    </div>

    @if($selectedExpense)
       <div class="space-y-4 sm:space-y-6">
          <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
             <h4 class="font-medium text-gray-900 text-sm sm:text-base">{{ __('Detalles del Gasto') }}</h4>
             <div class="mt-2 flex items-center gap-2 sm:gap-3">
                <span class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-900 font-medium text-xs sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-sm sm:text-base break-words">{{ $selectedExpense->description }}</p>
                    <p class="text-xs sm:text-sm text-gray-600">${{ number_format($selectedExpense->amount, 2) }}</p>
                </div>
             </div>
          </div>

          @livewire('notes.notes-displayer', [
             'notable_type' => \App\Models\Expense::class,
             'notable_id' => $selectedExpense->id
          ], key('notes-'.$selectedExpense->id))
       </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4">
       <flux:button wire:click="$set('showViewModal', false)" variant="primary" class="w-full sm:w-auto text-sm sm:text-base">
          {{ __('Cerrar') }}
       </flux:button>
    </div>
</flux:modal>