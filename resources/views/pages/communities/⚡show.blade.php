<?php

use App\Livewire\Concerns\HasCommunityFavoriteToggle;
use Livewire\Component;
use App\Models\Community;
use Livewire\WithPagination;

new class extends Component {
    use HasCommunityFavoriteToggle;
    use WithPagination;
    public Community $community;
    // public $events;

    public function mount(Community $community)
    {
        $this->community = $community;
        $this->initializeCommunityFavoriteState($this->community);
        // $this->events = $community->events()->latest()->paginate(8);
    }

    public function with(): array
    {
        return [
            'events' => $this->community->events()
                ->with(['address_book.city', 'address_book.province'])
                ->latest()
                ->paginate(5),
        ];
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => $this->community->name]);
    }
}; ?>

<div class="page">
    <div class="flex justify-between items-start mt-4">
        <div class="flex space-x-4">
            <img src="{{ $community->logo_img }}" alt="" class="w-13 h-13 rounded-full">
            <h1 class="text-2xl font-anta">{{ $community->name }}</h1>
        </div>
        @if (!$community->is_mine)
            <img src="{{ $isCommunityFavorited ? '/icons/heart-pink-fill.svg' : '/icons/heart-pink-empty.svg' }}"
                alt="favorite" class="w-5 cursor-pointer" wire:click="toggleCommunityFavorite">
        @endif
    </div>
    @if ($community->website || $community->linkedin || $community->instagram || $community->facebook)
        <div class="mt-5 flex space-x-1.5 items-center">
            @if ($community->website)
                <a href="{{ $community->website }}" target="_blank">
                    <img src="/icons/website-cyan.svg" alt="" class="w-4 mr-4">
                </a>
            @endif
            @if ($community->linkedin)
                <a href="{{ $community->linkedin }}" target="_blank">
                    <img src="/icons/linkedin-cyan.svg" alt="" class="w-4 mr-4">
                </a>
            @endif
            @if ($community->instagram)
                <a href="{{ $community->instagram }}" target="_blank">
                    <img src="/icons/instagram-cyan.svg" alt="" class="w-3.5 mr-4">
                </a>
            @endif
            @if ($community->facebook)
                <a href="{{ $community->facebook }}" target="_blank">
                    <img src="/icons/facebook-cyan.svg" alt="" class="w-3.5 mr-4">
                </a>
            @endif
        </div>
    @endif
    <div class="mt-5" x-data="{ expanded: false }">
        <div :class="expanded ? '' : 'line-clamp-4'" class="text-sm transition-ll duration-300">
            <p>{{ $community->description }}</p>
        </div>
        @if (strlen($community->description) > 100)
            <button @click="expanded = !expanded" class="text-cyan text-xs mt-1 underline focus:outline-none"
                x-text="expanded ? '{{ __('communities.show_less') }}' : '{{ __('communities.show_more') }}'">
            </button>
        @endif

    </div>
    @if ($events->count() > 0)
        <div class="mt-8">
            @foreach ($events as $event)
                <livewire:event-mini-card :event="$event" />
            @endforeach
        </div>
        <div class="mt-4">
            {{ $events->links('livewire.custom-pagination') }}
        </div>
    @endif
</div>
