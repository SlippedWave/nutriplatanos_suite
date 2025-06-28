<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Mount the component and redirect if already authenticated
     */
    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
            'email' => __('Las credenciales proporcionadas no coinciden con nuestros registros.'),
            ]);
        }
        
        // Check if user is active
        if (!Auth::user()->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            
            throw ValidationException::withMessages([
            'email' => __('Tu cuenta está inactiva. Contacta con el administrador.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        
        // Update last login information
        Auth::user()->updateLoginTracking(request()->ip());
        
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

<div class="flex flex-col gap-3">
    <x-auth-header :title="__('Iniciar sesión en tu cuenta')" :description="__('Ingresa tu correo electrónico y contraseña para iniciar sesión')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col space-y-3">
        <!-- Email Address -->
        <flux:field>
            <flux:label class="text-accent-foreground">{{ __('Correo electrónico') }}</flux:label>
            <flux:input wire:model="email" type="email" required autofocus autocomplete="email"
                placeholder="email@ejemplo.com" class="text-[var(--color-text)]!" />
            <flux:error name="email" />
        </flux:field>


        <flux:field>
            <flux:label>{{ __('Contraseña') }}</flux:label>
            <flux:input wire:model="password" type="password" required autocomplete="current-password"
                :placeholder="__('Contraseña')" viewable class="text-[var(--color-text)]!" />
            <flux:error name="password" />
        </flux:field>

        <!-- Remember Me -->
        <flux:field variant="inline">
            <flux:checkbox wire:model="remember" />
            <flux:label>{{ __('Recordarme') }}</flux:label>
        </flux:field>

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full text-[var(--color-text)]!">
                {{ __('Iniciar sesión') }}</flux:button>
        </div>
    </form>


</div>
