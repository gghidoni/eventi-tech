<?php

use App\Livewire\Actions\Logout;
use Livewire\Component;

new class extends Component {
    public function logout(Logout $logout)
    {
        $logout();
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('auth.thanks.title')]);
    }
}; ?>

<div class="flex flex-col gap-6 mt-14 page">
    <p class="text-center text-gray-400">
        {{ __('auth.thanks.message') }}
    </p>
    <p class="text-center text-gray-400">
        {{ __('auth.verify_email.intro') }}
    </p>

    <div class="flex flex-col items-center justify-between space-y-3">
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
