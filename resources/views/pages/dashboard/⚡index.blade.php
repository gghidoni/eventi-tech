<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Event;
use App\Enums\EventStatus;

new class extends Component {
    public User $user;
    public int $pendingEvents = 0;
    public int $activeEvents = 0;
    public int $bookmarksCount = 0;

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('dashboard.title')]);
    }

    public function mount()
    {
        $this->user = auth()->user();
        $user = $this->user;
        
        $this->bookmarksCount = $this->user->bookmarks()->count();

        $this->pendingEvents = Event::whereHas('community', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('status', EventStatus::Pending->value)
            ->count();
        $this->activeEvents = Event::whereHas('community', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('status', EventStatus::Active->value)
            ->count();
    }
}; ?>

<div class="page">
    <h1 class="text-2xl">{{ __('dashboard.title') }}</h1>
    <div class="mt-6 flex space-x-5">

        {{-- Card eventi preferiti --}}
        <div class="relative w-1/2 border rounded-sm border-gray-600 flex flex-col p-3 space-y-3 h-32">
            <div class="flex items-baseline space-x-2">
                <span class="text-[40px] font-bold leading-none">{{ $bookmarksCount }}</span>
                <span class="text-sm">eventi</span>
            </div>
            <div class="text-sm text-gray-500 m-0">
                <span>preferiti</span>
            </div>
            <div class="absolute top-3 right-3">
                <img src="/icons/heart-pink-empty.svg" class="size-5" alt="Preferiti">
            </div>
        </div>


        {{-- TODO community seguite --}}
        <div class="relative w-1/2 border rounded-sm border-gray-600 flex flex-col p-3 space-y-3 h-32">
            <div class="flex items-baseline space-x-2">
                <span class="text-[40px] font-bold leading-none">{{ $bookmarksCount }}</span>
                <span class="text-sm">eventi</span>
            </div>
            <div class="text-sm text-gray-500 m-0">
                <span>preferiti</span>
            </div>
            <div class="absolute top-3 right-3">
                <img src="/icons/heart-pink-empty.svg" class="size-5" alt="Preferiti">
            </div>
        </div>
    </div>
    <div class="mt-6">
        <ul>
                <li class="text-cyan underline">
                    <x-menu-item icon="heart-cyan-empty" label="{{ __('dashboard.links.my_favorites') }}"
                        url="{{ route('dashboard.bookmarks') }}" />
                </li>
        </ul>
    </div>

    @if ($user->has_active_community)
        <div class="mt-15 flex space-x-5">

             {{-- Card eventi in pending --}}
            <div class="relative w-1/2 border rounded-sm border-gray-600 flex flex-col p-3 space-y-3 h-32">
                <div class="flex items-baseline space-x-2">
                    <span class="text-[40px] font-bold leading-none">{{ $pendingEvents }}</span>
                    <span class="text-sm">{{ __('dashboard.cards.favorites.events') }}</span>
                </div>
                <div class="text-sm text-gray-500 m-0">
                    <span>{{ __('dashboard.cards.pending_events.label') }}</span>
                </div>
                <div class="absolute top-3 right-3">
                    <img src="/icons/clock-pink.svg" class="size-5" alt="Preferiti">
                </div>
            </div>


             {{-- Card eventi attivi --}}
            <div class="relative w-1/2 border rounded-sm border-gray-600 flex flex-col p-3 space-y-3 h-32">
                <div class="flex items-baseline space-x-2">
                    <span class="text-[40px] font-bold leading-none">{{ $activeEvents }}</span>
                    <span class="text-sm">{{ __('dashboard.cards.favorites.events') }}</span>
                </div>
                <div class="text-sm text-gray-500 m-0">
                    <span>{{ __('dashboard.cards.active_events.label') }}</span>
                </div>
                <div class="absolute top-3 right-3">
                    <img src="/icons/calendar-cyan.svg" class="size-5" alt="Preferiti">
                </div>
            </div>
        </div>
        <div class="mt-6">
            <ul>
                <li class="text-cyan underline mb-2">
                    <x-menu-item icon="calendar-cyan" label="{{ __('dashboard.links.all_my_events') }}"
                        url="{{ route('dashboard.bookmarks') }}" />
                </li>
                <li class="text-cyan underline mb-2">
                    <x-menu-item icon="plus-cyan" label="{{ __('dashboard.links.create_event') }}" url="" />
                </li>
                <li class="text-cyan underline mb-2">
                    <x-menu-item icon="users-cyan" label="{{ __('dashboard.links.my_communities') }}" url="{{ route('dashboard.communities.index') }}" />
                </li>
            </ul>
        </div>
    @endif
</div>
