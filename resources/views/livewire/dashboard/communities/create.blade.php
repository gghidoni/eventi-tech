<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\Community;
use App\Actions\CreateCommunity;

new class extends Component {
    #[Validate(['required'])]
    public string $name = '';

    #[Validate(['required'])]
    public string $description = '';

    #[Validate(['sometimes', 'url'])]
    public string $website = '';

    #[Validate(['sometimes', 'url'])]
    public string $linkedin = '';

    #[Validate(['sometimes', 'url'])]
    public string $instagram = '';

    #[Validate(['sometimes', 'url'])]
    public string $facebook = '';

    #[Validate([])]
    public string $phone = '';

    #[Validate([])]
    public string $logo = '';

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('Community')]);
    }

    public function save(CreateCommunity $action)
    {
        $data = $this->validate();

        $action->execute($data);

        $this->dispatch('messageSent', message: 'Community creata con successo, attendi l\'approvazione', success: true);

        return redirect()->route('dashboard.communities.index');
    }
}; ?>

<div class="page">
    <h1 class="text-xl">crea una community</h1>
    <form wire:submit="save" class="mt-5">

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="block text-sm font-medium mb-1">nome</label>
            <input type="text" id="name" name="name" class="input-et" wire:model="name" />
            @error('name')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Descrizione --}}
        <div class="mb-3">
            <label for="description" class="block text-sm font-medium mb-1">descrizione</label>
            <textarea id="description" name="description" class="textarea-et !pt-1.5" wire:model="description" rows="6"></textarea>
            @error('description')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Sito web --}}
        <div class="mb-3">
            <label for="website" class="block text-sm font-medium mb-1">sito web</label>
            <input type="text" id="website" name="website" class="input-et" wire:model="website" />
            @error('website')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Linkedin --}}
        <div class="mb-3">
            <label for="linkedin" class="block text-sm font-medium mb-1">linkedin</label>
            <input type="text" id="linkedin" name="linkedin" class="input-et" wire:model="linkedin" />
            @error('linkedin')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Instagram --}}
        <div class="mb-3">
            <label for="instagram" class="block text-sm font-medium mb-1">instagram</label>
            <input type="text" id="instagram" name="instagram" class="input-et" wire:model="instagram" />
            @error('instagram')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Facebook --}}
        <div class="mb-3">
            <label for="facebook" class="block text-sm font-medium mb-1">facebook</label>
            <input type="text" id="facebook" name="facebook" class="input-et" wire:model="facebook" />
            @error('facebook')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Telefono --}}
        <div class="mb-3">
            <label for="phone" class="block text-sm font-medium mb-1">telefono</label>
            <input type="text" id="phone" name="phone" class="input-et" wire:model="phone" />
            @error('phone')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Logo --}}
        <div class="mb-3">
            <label for="logo" class="block text-sm font-medium mb-1">logo</label>
            <input type="text" id="logo" name="logo" class="input-et" wire:model="logo" />
            @error('logo')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>crea</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>
