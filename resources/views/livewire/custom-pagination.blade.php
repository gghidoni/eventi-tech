<div class="mt-8">
    @if ($paginator->hasPages())
        <nav class="flex justify-between items-center">
            {{-- Pulsante Precedente --}}
            @if ($paginator->onFirstPage())
                <button class="flex items-center space-x-2 text-gray-500 underline text-sm"><span>indietro</span></button>
            @else
                <button wire:click="previousPage" class="flex items-center space-x-2 text-cyan underline text-sm"><img src="/icons/left-cyan.svg" alt=""><span>indietro</span></button>
            @endif

            <span class="text-sm">p.{{ $paginator->currentPage() }}</span>

            {{-- Pulsante Successivo --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="flex items-center space-x-2 text-cyan underline text-sm"><span>avanti</span><img src="/icons/right-cyan.svg" alt=""></button>
            @else
                <button class="flex items-center space-x-2 text-gray-500 underline text-sm"><span>avanti</span></button>
            @endif
        </nav>
    @endif
</div>