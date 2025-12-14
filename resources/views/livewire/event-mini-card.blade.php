<?php

use Livewire\Volt\Component;

new class extends Component {
    public \App\Models\Event $event;
    public bool $menuOpen = false;

    public function openMenu()
    {
        $this->menuOpen = true;
    }

    public function closeMenu()
    {
        $this->menuOpen = false;
    }

    public function addBookmark()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
    }

};

?>

<div class="flex pt-2 mb-5 w-full">

    <a :href="'/events'" class="w-22 rounded-md flex-shrink-0">
        <!--- h-28??? -->
        <img src="{{ $event->poster_img }}" alt="" class="rounded-md h-28" />

    </a>

    <div class="flex flex-col justify-between pl-2.5 pr-2 w-full">
        <a hrfe="#">
            <div class="flex flex-col">
                <span class="text-[9px] text-white opacity-70">{{ trans('titles.event.type.' . $event->type) }}</span>
                <h3 class="text-pink font-anta leading-[18px]">{{ $event->title }}</h3>
            </div>
        </a>
        <div class="">
            <div class="flex items-center">
                <img src="/icons/calendar-cyan.svg" alt="" class="!w-3.5 mr-2" />
                <span class="text-white font-anta text-sm">{{ $event->formatted_start_date }}</span>
            </div>
            <div class="flex justify-between">
                <div class="flex items-center">
                    <img src="/icons/location-cyan.svg" alt="" class="!w-3.5 mr-2" />
                    <span class="text-white font-anta text-sm">{{ $event->address_book->city->name }},
                        {{ $event->address_book->province->code }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="relative">
        <img src="/icons/kebab-white.svg" class="w-7 cursor-pointer pt-3" alt="event menu" wire:click="openMenu">

        @if ($menuOpen)
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10" wire:click.outside="closeMenu">
                <div class="py-1">
                    @if((auth()->check() && !auth()->user()->bookmarks()->where('event_id', $event->id)->exists()) || !auth()->check())
                        <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100" wire:click="addBookmark">
                            Aggiungi ai preferiti
                        </span>
                    @else
                        <span class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100" wire:click="removeBookmark">
                            Rimuovi dai preferiti
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
