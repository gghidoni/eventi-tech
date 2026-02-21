<?php

use App\Livewire\Concerns\HasBookmarkToggle;
use Livewire\Component;

new class extends Component
{
    use HasBookmarkToggle;

    public App\Models\Event $event;

    public bool $menuOpen = false;

    public function openMenu()
    {
        $this->menuOpen = true;
    }

    public function closeMenu()
    {
        $this->menuOpen = false;
    }

    public function mount()
    {
        $this->initializeBookmarkState($this->event);
    }
};

?>

<div class="flex mb-5 w-full glass-card px-3 pt-3 pb-3 shadow-lg h-34">

    <a href="{{ $event->public_url }}" class="w-22 rounded-md flex-shrink-0" wire:navigate>
        <picture>
            <source media="(min-width: 1024px)" srcset="{{ $event->poster_mobile_img }}">

            <img src="{{ $event->poster_thumb_img }}" alt="{{ $event->title }}"
                class="h-18 w-18 object-cover object-top-left rounded-md bg-gray-700" loading="lazy" />
        </picture>
    </a>

    <div class="flex flex-col justify-between pr-2 w-full">
        <a href="{{ $event->public_url }}" wire:navigate>
            <div class="flex flex-col">
                <div class="text-[10px] text-white opacity-70 flex space-x-1">
                    <span>{{ trans('titles.event.type.' . $event->type->value) }}</span>
                </div>
                <h3 class="font-anta leading-[18px] line-clamp-2 font-bold" title="{{ $event->title }}">
                    {{ $event->title }}</h3>
            </div>
        </a>
        <div class="">
            <div class="flex items-center space-x-1 mb-1 mt-1.5 ml-[-2px]">
                <img class="rounded-full w-4" src="{{ $event->community->logo_img }}" alt="">
                <span class="text-xs font-anta opacity-80">{{ $event->community->name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="/icons/calendar-cyan.svg" alt="" class="!w-2.5 mr-2" />
                    <span class="text-white font-anta text-xs opacity-80">{{ $event->formatted_start_date }}</span>
                </div>
                <div class="flex items-center space-x-1 mr-3">
                    @foreach ($event->tags as $tag)
                        <span class="rounded-full p-1"
                            {{-- Colori dinamici dal DB: inline style evita i limiti di compilazione classi Tailwind dinamiche --}}
                            style="background-color: {{ $tag->badge_color }}; color: {{ $tag->label_color }};">
                            @if ($tag->icon)
                                {{-- L'icona è uno slug Simple Icons valido salvato nel DB. --}}
                                <img src="https://cdn.simpleicons.org/{{ rawurlencode(strtolower(trim($tag->icon))) }}/{{ ltrim($tag->label_color, '#') }}"
                                    alt="" class="w-2.5 h-2.5 shrink-0" loading="lazy">
                            @endif
                        </span>
                    @endforeach
                </div>

            </div>
            @if ($event->address_book_id)
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <img src="/icons/location-cyan.svg" alt="" class="!w-2.5 mr-2" />
                        <span class="text-white font-anta text-xs opacity-80">{{ $event->address_book->city->name }},
                            {{ $event->address_book->province->code }}</span>
                    </div>
                </div>
            @else 
                <div class="flex items-center">
                    <img src="/icons/location-cyan.svg" alt="" class="!w-2.5 mr-2" />
                    <span class="text-white font-anta text-xs opacity-80">Online</span>
                </div>
            @endif
        </div>
    </div>
    <div class="relative flex flex-col justify-between pb-1">
        <img src="/icons/kebab-white.svg" class="w-7 cursor-pointer" alt="event menu" wire:click="openMenu">

        @if ($menuOpen)
            <div class="absolute right-0 top-6.5 mt-1 w-48 glass-panel z-10"
                wire:click.outside="closeMenu">
                <div class="py-1">
                    <a class="block px-4 py-2 text-xs text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer"
                        href="{{ $event->public_url }}" wire:navigate>
                        {{ __('common.actions.open') }}
                    </a>
                    @if (!$event->is_mine)
                        @if (!$isBookmarked)
                            <span class="block px-4 py-2 text-xs text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer"
                                wire:click="toggleBookmark">
                                {{ __('events.actions.add_bookmark') }}
                            </span>
                        @else
                            <span class="block px-4 py-2 text-xs text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer"
                                wire:click="toggleBookmark">
                                {{ __('events.actions.remove_bookmark') }}
                            </span>
                        @endif
                    @else
                        <a class="block px-4 py-2 text-xs text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer"
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
