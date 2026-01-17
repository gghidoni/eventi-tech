<div class="mt-6">

    {{-- Title Search --}}
    <div class="relative w-100% text-gray-300">
        <input type="text" placeholder="{{ __('events.search.placeholder') }}" class="input-et" wire:model.live.debounce.500ms="query" />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
            <img src="/icons/lente-cyan.svg" alt="" class="w-4.5">
        </div>
    </div>

    {{-- Location Search --}}
    <div style="relative w-100% mt-4 text-gray-300">
        <livewire:select.location name="location" wire:model.live="location" :endpoint="'http://nginx/find-location'"
            placeholder="{{ __('events.search.location_placeholder') }}" :extra-params="['type' => 'all']" />
    </div>


    {{-- Eventi --}}
    <div class="mt-10">
        @forelse($events as $event)

            {{-- Event card --}}
            <livewire:event-mini-card :event="$event" wire:key="{{$event->id}}" />

        @empty
            <p class="text-gray-500">{{ __('events.search.no_results') }}</p>
        @endforelse
    </div>

    {{-- Paginazione --}}
    <div class="mt-6">
        {{ $events->links('livewire.custom-pagination') }}
    </div>
</div>