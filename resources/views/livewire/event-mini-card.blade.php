<?php

use Livewire\Component;
use App\Actions\ToggleBookmark;

new class extends Component {
    public \App\Models\Event $event;
    public bool $menuOpen = false;
    public bool $isBookmarked = false;

    public function openMenu()
    {
        $this->menuOpen = true;
    }

    public function closeMenu()
    {
        $this->menuOpen = false;
    }

    public function toggleBookmark(ToggleBookmark $action)
    {
        // Utente non loggato
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Utente loggato ma NON verificato
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        try {
            $isBookmarked = $action->execute(auth()->user(), $this->event->id);
            $this->isBookmarked = $isBookmarked;
            $this->menuOpen = false;

            if (!$this->isBookmarked) {
                $this->dispatch('bookmarkUpdated');
            }

            $message = $this->isBookmarked ? __('events.messages.bookmarked') : __('events.messages.unbookmarked');
            $this->dispatch('messageSent', message: $message, success: true);
        } catch (\Throwable $th) {
            $message = __('common.error');
            $this->dispatch('messageSent', message: $message, success: false);
        }
    }

    public function mount()
    {
        if ($this->event->getAttribute('is_bookmarked') !== null) {
            $this->isBookmarked = (bool) $this->event->is_bookmarked;
        } else {
            $this->isBookmarked = auth()->check() && auth()->user()->bookmarks()->where('event_id', $this->event->id)->exists();
        }
    }
};

?>

<div class="flex mb-5 w-full bg-white/8 backdrop-blur-md rounded-lg px-3 pt-3 pb-3 shadow-lg h-28">

    <a href="{{ $event->public_url }}" class="w-22 rounded-md flex-shrink-0" wire:navigate>
        <picture>
            <source media="(min-width: 1024px)" srcset="{{ $event->poster_mobile_img }}">

            <img src="{{ $event->poster_thumb_img }}" alt="{{ $event->title }}"
                class="h-18 w-18 object-cover rounded-md bg-gray-700" loading="lazy" />
        </picture>
    </a>

    <div class="flex flex-col justify-between pr-2 w-full">
        <a href="{{ $event->public_url }}" wire:navigate>
            <div class="flex flex-col">
                <div class="text-[10px] text-white opacity-70 flex space-x-1">
                    <span>{{ trans('titles.event.type.' . $event->type->value) }}</span>
                    @if ($event->is_mine)
                        <span
                            class="text-xs text-cyan opacity-70 uppercase">
                            @if($event->status->value === \App\Enums\EventStatus::Active->value)
                                <img src="/icons/accept.svg" alt="" class="!w-3.5" />
                            @elseif($event->status->value === \App\Enums\EventStatus::Pending->value)
                                <img src="/icons/pending.svg" alt="" class="!w-3.5" />
                            @elseif($event->status->value === \App\Enums\EventStatus::Terminate->value)
                                <img src="/icons/terminate.svg" alt="" class="!w-3.5" />
                            @else
                                <img src="/icons/reject.svg" alt="" class="!w-3.5" />
                            @endif
                        </span>
                    @endif
                </div>
                <h3 class="text-pink font-anta leading-[18px] line-clamp-2" title="{{ $event->title }}">
                    {{ $event->title }}</h3>
            </div>
        </a>
        <div class="">
            <div class="flex items-center">
                <img src="/icons/calendar-cyan.svg" alt="" class="!w-2.5 mr-2" />
                <span class="text-white font-anta text-xs">{{ $event->formatted_start_date }}</span>
            </div>
            @if ($event->address_book_id)
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <img src="/icons/location-cyan.svg" alt="" class="!w-2.5 mr-2" />
                        <span class="text-white font-anta text-xs">{{ $event->address_book->city->name }},
                            {{ $event->address_book->province->code }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="relative flex flex-col justify-between pb-1">
        <img src="/icons/kebab-white.svg" class="w-7 cursor-pointer pt-2" alt="event menu" wire:click="openMenu">

        @if ($menuOpen)
            <div class="absolute right-0 top-6.5 mt-1 w-48 bg-white rounded-md shadow-lg z-10"
                wire:click.outside="closeMenu">
                <div class="py-1">
                    <a class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                        href="{{ $event->public_url }}" wire:navigate>
                        {{ __('common.actions.open') }}
                    </a>
                    @if (!$event->is_mine)
                        @if (!$isBookmarked)
                            <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                wire:click="toggleBookmark">
                                {{ __('events.actions.add_bookmark') }}
                            </span>
                        @else
                            <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                wire:click="toggleBookmark">
                                {{ __('events.actions.remove_bookmark') }}
                            </span>
                        @endif
                    @else
                        <a class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                            href="{{ $event->edit_url }}" wire:navigate>
                            {{ __('common.actions.edit') }}
                        </a>
                    @endif
                </div>
            </div>
        @endif
        @if (!$event->is_mine)
            <img src="{{ $isBookmarked ? '/icons/heart-pink-fill.svg' : '/icons/heart-pink-empty.svg' }}"
                alt="bookmark" class="w-4" wire:click="toggleBookmark">
        @endif
    </div>
</div>
