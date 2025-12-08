<div class="mt-6">

    <div class="relative max-w-sm text-gray-300">
        <input type="text" placeholder="titolo, argomento..." class="input-et" wire:model.live.debounce.500ms="query" />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
            <img src="/icons/lente-cyan.svg" alt="" class="w-5">
        </div>
    </div>


    <div style="max-width: 28rem;">
        <livewire:location name="location" wire:model.live="location" :endpoint="'http://nginx/find-location'"
            placeholder="dove?" />
    </div>


    <div class="mt-4">
        @forelse($results as $event)
            <div class="p-4 mb-2 border rounded">
                <h3 class="font-bold">{{ $event->title }}</h3>
                <p class="text-gray-600">{{ $event->description }}</p>
            </div>
        @empty
            <p class="text-gray-500">Nessun evento trovato</p>
        @endforelse
    </div>

    @if ($results?->hasPages())
        <div class="mt-4">
            {{ $results->links() }}
        </div>
    @endif
</div>
