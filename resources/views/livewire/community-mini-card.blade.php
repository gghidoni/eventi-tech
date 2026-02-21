<?php

use App\Livewire\Concerns\HasCommunityFavoriteToggle;
use Livewire\Component;

new class extends Component
{
    use HasCommunityFavoriteToggle;

    public App\Models\Community $community;

    public function mount()
    {
        $this->initializeCommunityFavoriteState($this->community);
    }
};

?>

<div class="flex mb-5 w-full glass-card px-3 py-3 shadow-lg items-center">
    <a href="{{ $community->public_url }}" wire:navigate>
        <img src="{{ $community->logo_img }}" alt="{{ $community->name }}" class="w-10 h-10 rounded-full">
    </a>

    <div class="ml-3 flex-1">
        <a href="{{ $community->public_url }}" wire:navigate>
            <h3 class="font-anta leading-5 font-bold">{{ $community->name }}</h3>
        </a>
        <a href="{{ $community->public_url }}" wire:navigate class="text-cyan text-xs underline">
            {{ __('common.actions.open') }}
        </a>
    </div>

    <img src="{{ $isCommunityFavorited ? '/icons/heart-pink-fill.svg' : '/icons/heart-pink-empty.svg' }}"
        alt="favorite" class="w-4 cursor-pointer" wire:click="toggleCommunityFavorite">
</div>
