<x-layouts.base :title="__('Grazie per esserti registrato')" isDashboard="false">
    <div class="flex flex-col gap-6 mt-14 page">
        <flux:text class="text-center">
            {{ __('Grazie per esserti registrato!') }}
        </flux:text>
        <flux:text class="text-center">
            {{ __('Per favore verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.') }}
        </flux:text>
        <div class="flex flex-col items-center justify-between space-y-3">
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
