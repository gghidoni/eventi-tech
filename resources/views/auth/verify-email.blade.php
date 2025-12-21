<x-layouts.base :title="__('Verifica il tuo indirizzo email')" isDashboard="false">
    <div class="flex flex-col gap-6 mt-14 page">
        <flux:text class="text-center">
            {{ __('Per favore verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-cyan">
                {{ __('Un nuovo link di verifica è stato inviato all’indirizzo email fornito durante la registrazione.') }}
            </flux:text>
        @endif


        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8 text-sm">
                    {{ __('invia di nuovo l\'email') }}
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </button>
                {{-- <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full text-xs"
                    icon:trailing="arrow-turn-down-left"
                    icon:variant="micro"
                >
                    {{ __('Invia di nuovo l\'email') }}
                </flux:button> --}}
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button
                    variant="ghost"
                    type="submit"
                    class="text-xs cursor-pointer"
                    data-test="logout-button"
                    icon="arrow-right-start-on-rectangle"
                    icon:variant="micro"
                >
                    {{ __('Esci') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts.base>
