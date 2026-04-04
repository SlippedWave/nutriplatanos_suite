<div>
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'cameras-table')
        @php
            $type = data_get($flash, 'type', 'info');
        @endphp

        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition @class([
            'px-4 py-3 rounded-lg flex justify-between items-center',
            'bg-green-50 border border-green-200 text-green-700' => $type === 'success',
            'bg-danger-50 border border-danger-200 text-danger-700' =>
                $type === 'error',
            'bg-yellow-50 border border-yellow-200 text-yellow-700' =>
                $type === 'warning',
            'bg-blue-50 border border-blue-200 text-blue-700' => !in_array($type, [
                'success',
                'error',
                'warning',
            ]),
        ])>
            <div>{{ data_get($flash, 'text') }}</div>
            <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
                <span class="sr-only">Cerrar</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 sm:p-4 mb-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1"></div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:select wire:model.live="perPage" class="w-20">
                    <option value="3">3</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                </flux:select>
                <flux:button variant="primary" icon="plus" wire:click="$dispatch('open-create-product-modal')"
                    class="w-full xs:w-auto">
                    <span class="hidden sm:inline">{{ __('Nuevo producto') }}</span>
                    <span class="sm:hidden">{{ __('Nueva') }}</span>
                </flux:button>
            </div>
        </div>
    </div>


    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('name')"
                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-start justify-start">
                                <span>Producto</span>
                                @if ($sortField === 'name')
                                    <flux:icon.chevron-up
                                        class="w-4 h-4 ml-1 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            <div class="flex items-center justify-center">
                                <span>Descripción</span>
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            <div class="flex items-center justify-center">
                                <span>Acciones</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-start">
                                {{ $product->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $product->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <flux:button variant="ghost" size="sm" icon="pencil"
                                    wire:click="$dispatch('open-update-product-modal', {id: {{ $product->id }}})"
                                    aria-label="{{ __('Editar producto') }}" />
                                <flux:button variant="ghost" size="sm" icon="trash"
                                    wire:click="$dispatch('open-delete-product-modal', {id: {{ $product->id }}})"
                                    aria-label="{{ __('Eliminar producto') }}" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay productos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:resources.products.create-product-modal />
    <livewire:resources.products.update-product-modal />
    <livewire:resources.products.delete-product-modal />
</div>
