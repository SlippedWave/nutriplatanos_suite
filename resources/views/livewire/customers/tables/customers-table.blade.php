<div class="space-y-6">
    <!-- Flash Messages -->
    @php
        $flash = session('message');
    @endphp

    @if ($flash && data_get($flash, 'header') === 'customers-table')
        @php
            $type = data_get($flash, 'type', 'info');
        @endphp

        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition
            @class([
                'px-4 py-3 rounded-lg flex justify-between items-center',
                'bg-green-50 border border-green-200 text-green-700' => $type === 'success',
                'bg-danger-50 border border-danger-200 text-danger-700' => $type === 'error',
                'bg-yellow-50 border border-yellow-200 text-yellow-700' => $type === 'warning',
                'bg-blue-50 border border-blue-200 text-blue-700' => !in_array($type, ['success','error','warning']),
            ])
        >
            <div>{{ data_get($flash, 'text') }}</div>
            <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
                <span class="sr-only">Close</span>
                <flux:icon.x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 sm:p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar clientes..."
                        type="search"
                    >
                    </flux:input>
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <flux:button 
                        variant="primary" 
                        wire:click="toggleIncludeDeleted"
                        class="{{ $includeDeleted ? 'bg-gray-100! text-gray-900!' : 'bg-background! text-gray-500! hover:bg-gray-50!' }}" 
                        aria-label="{{ $includeDeleted ? __('Ocultar clientes eliminados') : __('Incluir clientes eliminados') }}"
                    >
                        {{ $includeDeleted ? __('Ocultar eliminados') : __('Incluir eliminados') }}
                    </flux:button>

                    <flux:select wire:model.live="perPage" class="w-20">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </flux:select>

                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        {{ __('Agregar Cliente') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('name')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Nombre
                                    @if($sortField === 'name')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('email')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Email
                                    @if($sortField === 'email')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('phone')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Teléfono
                                    @if($sortField === 'phone')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('box_balance')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Cajas
                                    @if($sortField === 'box_balance')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('address')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Dirección
                                    @if($sortField === 'address')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('rfc')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    RFC
                                    @if($sortField === 'rfc')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <button 
                                    wire:click="sortBy('created_at')" 
                                    class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                                >
                                    Fecha de Registro
                                    @if($sortField === 'created_at')
                                        @if($sortDirection === 'asc')
                                            <flux:icon.chevron-up class="size-3" />
                                        @else
                                            <flux:icon.chevron-down class="size-3" />
                                        @endif
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center">
                            <div class="flex justify-center">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium text-sm">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $customer->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                {{ $customer->email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                {{ $customer->phone ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                {{ $customer->getBoxBalance() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                {{ $customer->address ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                {{ $customer->rfc ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @if($customer->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center">
                                {{ $customer->created_at->format('d/m/Y') }}
                            </td>
                           <td class="px-6 py-4 max-w-[220px] text-center">
                                <div class="flex items-center gap-2 justify-center">
                                    @if ($customer->trashed())
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            wire:click="$dispatch('open-view-customer-modal', { customerId: {{ $customer->id }} })"
                                            aria-label="{{ __('Ver cliente') }}"
                                        />

                                        <div class="mt-2">
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full text-xs font-medium bg-danger-100 text-danger-800">
                                                {{ __('Cliente eliminado') }}
                                            </span>
                                        </div>
                                    @else
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil"
                                            wire:click="$dispatch('open-update-customer-modal', { customerId: {{ $customer->id }} })"
                                            aria-label="{{ __('Editar cliente') }}"
                                        />
                                        
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            wire:click="$dispatch('open-view-customer-modal', { customerId: {{ $customer->id }} })"
                                            aria-label="{{ __('Ver cliente') }}"
                                        />

                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash"
                                            class="text-danger-600 hover:text-danger-700 hover:bg-danger-50"
                                            wire:click="$dispatch('open-delete-customer-modal', { customerId: {{ $customer->id }} })"
                                            aria-label="{{ __('Eliminar cliente') }}"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <flux:icon.users class="size-12 text-gray-300 mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay clientes</h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        @if($search)
                                            No se encontraron clientes que coincidan con "{{ $search }}"
                                        @else
                                            Comienza agregando tu primer cliente.
                                        @endif
                                    </p>
                                    @if(!$search)
                                        <flux:button variant="primary" wire:click="$dispatch('open-create-customer-modal')">
                                            <flux:icon.plus class="size-4" />
                                            Agregar Cliente
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Results Info -->
    <div class="flex items-center justify-between text-sm text-gray-500">
        <div>
            Mostrando {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} 
            de {{ $customers->total() }} clientes
        </div>
        
        @if($search)
            <button 
                wire:click="$set('search', '')" 
                class="text-primary-600 hover:text-primary-700"
            >
                Limpiar búsqueda
            </button>
        @endif
    </div>

    <livewire:customers.create-customer-modal />
    <livewire:customers.update-customer-modal />
    <livewire:customers.view-customer-modal />
    <livewire:customers.delete-customer-modal />
</div>
