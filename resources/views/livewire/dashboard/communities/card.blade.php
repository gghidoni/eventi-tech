<?php

use Livewire\Volt\Component;

new class extends Component {
    public App\Models\Community $community;
    public bool $menuOpen = false;

    public function openMenu()
    {
        $this->menuOpen = true;
    }

    public function closeMenu()
    {
        $this->menuOpen = false;
    }
}; ?>

<div class="mb-5 flex">
    <img src="{{ $community->logo_img }}" alt="" class="w-10 h-10 rounded-full">
    <div class="flex flex-col ml-3">
        <span>{{ $community->name }}</span>
        <span class="text-cyan text-xs">@lang('titles.community.status.' . $community->status)</span>
    </div>
    <div class="relative ml-auto">
        <img src="/icons/kebab-white.svg" class="w-7 cursor-pointer pt-2" alt="event menu" wire:click="openMenu">

        @if ($menuOpen)
            <div class="absolute right-0 top-7.5 mt-1 w-48 bg-white rounded-md shadow-lg z-10"
                wire:click.outside="closeMenu">
                <div class="py-1">
                    <a class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 cursor-pointer" href="">
                        Apri
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
