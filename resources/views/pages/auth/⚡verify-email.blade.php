<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

new class extends Component {
    public function sendVerification()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard.index', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout)
    {
        $logout();
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('auth.verify_email.title')]);
    }
}; ?>

<div class="flex flex-col gap-6 mt-14 page">
    <p class="text-center text-gray-400">
        {{ __('auth.verify_email.intro') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-center font-medium text-cyan">
            {{ __('auth.verify_email.sent') }}
        </p>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <button wire:click="sendVerification"
            class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8 text-sm transition-colors font-medium cursor-pointer disabled:opacity-50">
            <span wire:loading.remove wire:target="sendVerification">{{ __('auth.verify_email.resend_button') }}</span>
            <span wire:loading wire:target="sendVerification">...</span>
            <img class="ml-3 w-3" src="/icons/right-black.svg" alt="" wire:loading.remove wire:target="sendVerification">
        </button>

        <button
            wire:click="logout"
            class="flex items-center gap-2 text-xs font-medium text-gray-400 hover:text-white transition-colors cursor-pointer"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
            {{ __('auth.verify_email.logout_button') }}
        </button>
    </div>
</div>
