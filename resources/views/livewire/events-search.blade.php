<div class="mt-6">

    <div class="relative w-100% text-gray-300">
        <input type="text" placeholder="titolo, argomento..." class="input-et" wire:model.live.debounce.500ms="query" />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
            <img src="/icons/lente-cyan.svg" alt="" class="w-4.5">
        </div>
    </div>


    <div style="relative w-100% mt-4 text-gray-300">
        <livewire:location name="location" wire:model.live="location" :endpoint="'http://nginx/find-location'"
            placeholder="dove?" />
    </div>


    <div class="mt-10">
        @forelse($events as $event)

            <livewire:event-mini-card :event="$event" wire:key="{{$event->id}}" />


            {{-- <div class="p-4 mb-2 border rounded">
                <h3 class="font-bold">{{ $event->title }}</h3>
                <p class="text-gray-600">{{ $event->description }}</p>
            </div> --}}
        @empty
            <p class="text-gray-500">Nessun evento trovato</p>
        @endforelse
    </div>

    {{-- @if ($results?->hasPages())
        <div class="mt-4">
            {{ $results->links() }}
        </div>
    @endif --}}
</div>