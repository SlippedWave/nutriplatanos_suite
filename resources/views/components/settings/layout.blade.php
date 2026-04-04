<div class="flex items-start max-md:flex-col max-w-full">
    <div class="w-full md:w-[220px] mb-2! py-0! md:py-2! md:mb-3 md:flex-shrink-0">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Perfil') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Contraseña') }}</flux:navlist.item>
            @if(auth()->user()->isAdmin())
                <flux:navlist.item :href="route('settings.users')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
            @endif
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6 min-w-0 max-w-full">
        <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <flux:heading>{{ $heading ?? '' }}</flux:heading>
                <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            </div>

            <div class="ml-4 flex-shrink-0">
                {{ $newUserButton ?? '' }}
            </div>
        </div>

        <div class="mt-5 w-full max-w-full">
            {{ $slot }}
        </div>
    </div>
</div>
