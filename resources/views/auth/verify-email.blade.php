<x-layouts.base :title="__('Verifica il tuo indirizzo email')">
    <div class="flex flex-col gap-6 mt-14 page">
        <p class="text-center text-gray-400">
            {{ __('Per favore verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <p class="text-center font-medium text-cyan">
                {{ __('Un nuovo link di verifica è stato inviato all’indirizzo email fornito durante la registrazione.') }}
            </p>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 mt-8 text-sm transition-colors font-medium">
                    {{ __('invia di nuovo l\'email') }}
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex items-center gap-2 text-xs font-medium text-gray-400 hover:text-white transition-colors cursor-pointer"
                    data-test="logout-button"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    {{ __('Esci') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.base>