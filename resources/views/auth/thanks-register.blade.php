<x-layouts.base :title="__('Grazie per esserti registrato')">
    <div class="flex flex-col gap-6 mt-14 page">
        <p class="text-center text-gray-400">
            {{ __('Grazie per esserti registrato!') }}
        </p>
        <p class="text-center text-gray-400">
            {{ __('Per favore verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.') }}
        </p>

        <div class="flex flex-col items-center justify-between space-y-3">
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