<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);
        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>


<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Gestión de usuarios')" :subheading="__('Administra los usuarios dentro del sistema')" :showSidebar="false">
        <div class="mt-6 w-full max-w-full overflow-hidden">
            <div class="overflow-x-auto">
                @livewire('settings.tables.users-table')
            </div>
        </div>
    </x-settings.layout>
</section>
