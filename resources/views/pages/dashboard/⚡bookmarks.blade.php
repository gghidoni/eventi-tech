<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    public string $tab = 'events';

    public function mount(): void
    {
        // Normalizza il tab dalla querystring con whitelist esplicita.
        $this->tab = $this->resolveTab(request()->query('tab'));
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('dashboard.bookmarks.title')]);
    }

    #[On('bookmarkUpdated')]
    public function refreshBookmarks(): void {}

    #[On('communityFavoriteUpdated')]
    public function refreshCommunityFavorites(): void {}

    protected function resolveTab(?string $tab): string
    {
        return in_array($tab, ['events', 'communities'], true) ? $tab : 'events';
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'events' => $this->tab === 'events'
                ? $user->bookmarks()->latest()->paginate(8, pageName: 'eventsPage')->withQueryString()
                : null,
            'communities' => $this->tab === 'communities'
                ? $user->favoriteCommunities()->latest()->paginate(8, pageName: 'communitiesPage')->withQueryString()
                : null,
        ];
    }
}; ?>

<div class="page">
    <h1 class="text-xl">{{ __('dashboard.bookmarks.heading') }}</h1>

    <div class="mt-6 inline-flex rounded-sm border border-gray-600 overflow-hidden text-center">
        <a href="{{ route('dashboard.bookmarks', ['tab' => 'events']) }}" wire:navigate
            class="px-4 py-2 text-sm w-1/2 {{ $tab === 'events' ? 'bg-white text-black' : 'text-white' }}">
            {{ __('dashboard.bookmarks.tabs.events') }}
        </a>
        <a href="{{ route('dashboard.bookmarks', ['tab' => 'communities']) }}" wire:navigate
            class="px-4 py-2 w-1/2 text-sm {{ $tab === 'communities' ? 'bg-white text-black' : 'text-white' }}">
            {{ __('dashboard.bookmarks.tabs.communities') }}
        </a>
    </div>

    @if ($tab === 'events')
        @if ($events?->isEmpty())
            <p class="text-gray-500 mt-8">{{ __('dashboard.bookmarks.empty_events') }}</p>
        @endif

        <div class="mt-8">
            @foreach ($events ?? [] as $event)
                <livewire:event-mini-card :event="$event" wire:key="event-{{ $event->id }}" />
            @endforeach
        </div>

        @if ($events)
            <div class="mt-4">
                {{ $events->links('livewire.custom-pagination') }}
            </div>
        @endif
    @else
        @if ($communities?->isEmpty())
            <p class="text-gray-500 mt-8">{{ __('dashboard.bookmarks.empty_communities') }}</p>
        @endif

        <div class="mt-8">
            @foreach ($communities ?? [] as $community)
                <livewire:community-mini-card :community="$community" wire:key="community-{{ $community->id }}" />
            @endforeach
        </div>

        @if ($communities)
            <div class="mt-4">
                {{ $communities->links('livewire.custom-pagination') }}
            </div>
        @endif
    @endif
</div>
