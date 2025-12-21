<x-layouts.base :title="__('Verify your email address')">
    <div class="flex flex-col gap-6 mt-14 page">
        <flux:text class="text-center">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-cyan">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full text-xs" icon:trailing="arrow-turn-down-left" icon:variant="micro">
                    {{ __('resend email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-xs cursor-pointer" data-test="logout-button" icon="arrow-right-start-on-rectangle" icon:variant="micro" >
                    {{ __('log out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts.base>