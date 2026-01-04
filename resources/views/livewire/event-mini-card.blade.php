<?php

use Livewire\Volt\Component;
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

            $message = $this->isBookmarked ? 'evento aggiunto ai preferiti' : 'evento rimosso dai preferiti';
            $this->dispatch('messageSent', message: $message, success: true);
        } catch (\Throwable $th) {
            $message = 'Si è verificato un errore';
            $this->dispatch('messageSent', message: $message, success: false);
        }
    }

    public function mount()
    {
        $this->isBookmarked = auth()->check() && auth()->user()->bookmarks()->where('event_id', $this->event->id)->exists();
    }
};

?>

<div class="flex pt-2 mb-5 w-full">

    <a href="{{ $event->public_url }}" class="w-22 rounded-md flex-shrink-0" wire:navigate>
        <picture>
            <source media="(min-width: 1024px)" srcset="{{ $event->poster_mobile_img }}">

            <img src="{{ $event->poster_thumb_img }}" alt="{{ $event->title }}"
                class="h-28 w-22 object-cover rounded-md bg-gray-700" loading="lazy" />
        </picture>
    </a>

    <div class="flex flex-col justify-between pl-2.5 pr-2 w-full pb-1">
        <a href="{{ $event->public_url }}" wire:navigate>
            <div class="flex flex-col">
                <span class="text-[9px] text-white opacity-70">{{ trans('titles.event.type.' . $event->type->value) }}</span>
                <h3 class="text-pink font-anta leading-[18px] line-clamp-2" title="{{ $event->title }}">
                    {{ $event->title }}</h3>
            </div>
        </a>
        <div class="">
            <div class="flex items-center mb-0.5">
                <img src="/icons/calendar-cyan.svg" alt="" class="!w-3 mr-2" />
                <span class="text-white font-anta text-xs">{{ $event->formatted_start_date }}</span>
            </div>
            <div class="flex justify-between">
                <div class="flex items-center">
                    <img src="/icons/location-cyan.svg" alt="" class="!w-3 mr-2" />
                    <span class="text-white font-anta text-xs">{{ $event->address_book->city->name }},
                        {{ $event->address_book->province->code }}</span>
                </div>
            </div>
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
                        apri
                    </a>
                    @if (!$event->is_mine)
                        @if (!$isBookmarked)
                            <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                wire:click="toggleBookmark">
                                aggiungi ai preferiti
                            </span>
                        @else
                            <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                                wire:click="toggleBookmark">
                                rimuovi dai preferiti
                            </span>
                        @endif
                    @else
                        <a class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer"
                            href="{{ $event->edit_url }}" wire:navigate>
                            modifica
                        </a>
                    @endif
                </div>
            </div>
        @endif
        @if (!$event->is_mine)
            <img src="{{ $isBookmarked ? '/icons/heart-pink-fill.svg' : '/icons/heart-pink-empty.svg' }}" alt="bookmark"
                class="w-4" wire:click="toggleBookmark">
        @endif
    </div>
</div>
