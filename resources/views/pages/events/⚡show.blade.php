<?php

use App\Livewire\Concerns\HasBookmarkToggle;
use App\Models\Event;
use Livewire\Component;

new class extends Component {
    use HasBookmarkToggle;

    public Event $event;

    public function mount(Event $event)
    {
        $this->event = $event->load(['community', 'address_book.city', 'address_book.province', 'address_book.region']);
        $this->initializeBookmarkState($this->event);
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => $this->event->title]);
    }
}; ?>

<div class="container mx-auto py-8 px-6 flex flex-col">
    <div>
        <h1 class="text-3xl font-anta font-bold">{{ $event->title }}</h1>
        <div class="flex justify-between pt-2">
            <div class="flex space-x-2 items-center">
                <a class="flex items-center space-x-1.5" href="{{ $event->community->public_url }}" wire:navigate>
                    <img src="{{ $event->community->logo_img }}" alt="" class="rounded-full w-7">
                    <span class="text-sm font-anta">{{ $event->community->name }}</span>
                </a>
            </div>
            <div class="flex space-x-2 items-center">
                @if ($event->address_book)
                    <img src="/icons/location-pink.svg" alt="" class="w-4">
                    <span class="text-white font-anta text-sm">{{ $event->address_book->city->name }},
                        {{ $event->address_book->province->code }}</span>
                @else
                    <span class="text-white font-anta text-sm">Online</span>
                @endif
            </div>
        </div>

        {{-- <div class="flex justify-between mt-3 items-center">
            <a class="flex items-center space-x-1.5" href="{{ $event->community->public_url }}" wire:navigate>
                <img src="{{ $event->community->logo_img }}" alt="" class="rounded-full w-7">
                <span class="text-sm font-anta">{{ $event->community->name }}</span>
            </a>
        </div> --}}
        <div class="flex space-x-2 mt-3">
            <img src="/icons/clock-pink.svg" alt="" class="w-5">
            <span class="text-sm font-anta">{{ $event->formatted_datetime_start }} -
                {{ $event->formatted_datetime_end }}</span>
        </div>
        <div class="flex space-x-2 mt-5">
            @foreach ($event->tags as $tag)
                <span class="text-xs font-anta px-1 py-1 rounded-md inline-flex items-center gap-1"
                    {{-- Colori dinamici dal DB: inline style evita i limiti di compilazione classi Tailwind dinamiche --}}
                    style="background-color: {{ $tag->badge_color }}; color: {{ $tag->label_color }};">
                    @if ($tag->icon)
                        {{-- L'icona è uno slug Simple Icons valido salvato nel DB. --}}
                        <img src="https://cdn.simpleicons.org/{{ rawurlencode(strtolower(trim($tag->icon))) }}/{{ ltrim($tag->label_color, '#') }}"
                            alt="" class="w-3 h-3 shrink-0" loading="lazy">
                    @endif
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
        <div class="mt-3">
            <p class="text-sm">{{ $event->description }}</p>
        </div>
        @if (!$event->is_mine)
            <div class="flex justify-end mt-2">
                <img src="{{ $isBookmarked ? '/icons/heart-pink-fill.svg' : '/icons/heart-pink-empty.svg' }}"
                    alt="bookmark" class="w-5" wire:click="toggleBookmark">
            </div>
        @endif
        {{-- Griglia a 2 colonne con gap: gestisce margini orizzontali e verticali in modo stabile. --}}
        <div class="mt-6 mb-8 grid w-full grid-cols-2 gap-3">
            @if ($event->tickets_url)
                <a href="{{ $event->tickets_url }}" target="_blank" rel="noopener" class="flex h-10 w-full items-center justify-center space-x-2 rounded-md border border-white px-2 py-1">
                    <img src="/icons/tickets-white.svg" alt="" class="w-4">
                    <span class="text-white text-xs uppercase font-anta">Biglietti</span>
                </a>
            @endif
            @if ($event->cfp_url)
                {{-- Countdown temporaneo CFP basato su start_date, senza nuovi campi DB. --}}
                <a href="{{ $event->cfp_url }}" target="_blank" rel="noopener" class="flex h-10 w-full items-center justify-center space-x-2 rounded-md border border-white px-2 py-1"
                    x-data="{
                        target: Date.parse(@js(optional($event->start_date)->toIso8601String())),
                        countdown: '',
                        update() {
                            if (Number.isNaN(this.target)) {
                                this.countdown = '--';
                                return;
                            }

                            const diff = this.target - Date.now();

                            if (diff <= 0) {
                                this.countdown = 'scaduto';
                                return;
                            }

                            const totalSeconds = Math.floor(diff / 1000);
                            const days = Math.floor(totalSeconds / 86400);
                            const hours = Math.floor((totalSeconds % 86400) / 3600);
                            const minutes = Math.floor((totalSeconds % 3600) / 60);
                            const seconds = totalSeconds % 60;

                            this.countdown = `${days}g ${hours}.${minutes}.${seconds}`;
                        }
                    }"
                    x-init="update(); setInterval(() => update(), 1000)">
                    <img src="/icons/mic-white.svg" alt="" class="w-4">
                    <span class="text-white text-xs uppercase font-anta">CFP</span>
                    <span class="text-pink text-[12px] font-anta whitespace-nowrap w-[64px]" x-text="countdown"></span>
                </a>
            @endif
            <a href="/" class="flex h-10 w-full items-center justify-center space-x-2 rounded-md border border-white px-2 py-1" target="_blank" rel="noopener">
                <img src="/icons/add-calendar-white.svg" alt="" class="w-5">
                <span class="text-white text-xs uppercase font-anta">Agg. al calendario</span>
            </a>
        </div>

        <picture>
            <source media="(max-width: 767px)" srcset="{{ $event->poster_mobile_img }}">

            <img src="{{ $event->poster_img }}" alt="{{ $event->title }}"
                class="w-full h-auto rounded-md flex-shrink-0 mt-3 bg-gray-700 shadow-xl" loading="eager">
        </picture>
    </div>
</div>
