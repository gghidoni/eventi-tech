<?php

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(CreateNewUser $creator)
    {
        $user = $creator->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('thanks-register');
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('auth.register.title')]);
    }
}; ?>

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-2xl/9 font-bold">{{ __('auth.register.title') }}</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm" x-data="{ showPassword: false, showConfirm: false }">

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="register" class="space-y-4">
            
            {{-- Nome --}}
            <div>
                <label for="name" class="block text-sm font-medium mb-1">{{ __('auth.register.name') }}</label>
                <input id="name" wire:model="name" type="text" class="input-et" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium mb-1">{{ __('auth.fields.email') }}</label>
                <input id="email" wire:model="email" type="email" class="input-et" required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium mb-1">{{ __('auth.fields.password') }}</label>
                <div class="relative">
                    <input id="password" wire:model="password" :type="showPassword ? 'text' : 'password'"
                        class="input-et" required>
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? '{{ __('auth.register.hide_password') }}' : '{{ __('auth.register.show_password') }}'">
                        <img x-show="!showPassword" src="/icons/eye-cyan.svg" alt="" class="!w-5">
                        <img x-show="showPassword" src="/icons/no-eye-cyan.svg" alt="" class="!w-5">
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Conferma Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">{{ __('auth.register.confirm_password') }}</label>
                <div class="relative">
                    <input id="password_confirmation" wire:model="password_confirmation"
                        :type="showConfirm ? 'text' : 'password'" class="input-et" required>
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                        @click="showConfirm = !showConfirm"
                        :aria-label="showConfirm ? '{{ __('auth.register.hide_password') }}' : '{{ __('auth.register.show_password') }}'">
                        <img x-show="!showConfirm" src="/icons/eye-cyan.svg" alt="" class="!w-5">
                        <img x-show="showConfirm" src="/icons/no-eye-cyan.svg" alt="" class="!w-5">
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="mt-10">
                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-4 disabled:opacity-50">
                    <span wire:loading.remove>{{ __('auth.register.button') }}</span>
                    <span wire:loading>...</span>
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="" wire:loading.remove>
                </button>
            </div>
        </form>

        {{-- Link a login --}}
        <p class="mt-4 text-center text-sm text-gray-600">
            {{ __('auth.register.already_registered') }}
            <a href="{{ route('login') }}" class="text-accent hover:underline">{{ __('auth.register.login_link') }}</a>
        </p>

    </div>
</div>
