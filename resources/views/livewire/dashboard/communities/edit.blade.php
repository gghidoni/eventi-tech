<?php

use Livewire\Volt\Component;
use App\Models\Community;
use Livewire\WithFileUploads;
use App\Actions\UpdateCommunity;
use Livewire\Attributes\Validate;

new class extends Component {
    use WithFileUploads;

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

    #[Validate(['nullable', 'image', 'max:1024'])]
    public $logo;

    public Community $community;

    public function mount(Community $community)
    {
        $this->community = $community;

        $this->fill($this->community->only(['name', 'description', 'website', 'linkedin', 'instagram', 'facebook', 'phone']));
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => $this->community->name]);
    }

    public function save(UpdateCommunity $action)
    {
        $data = $this->validate();

        if ($this->logo) {
            $data['logo'] = $this->logo->store('communities/logos', 'public');
        } else {
            unset($data['logo']);
        }

        $action->execute($this->community, $data);

        return redirect()->route('dashboard.communities.index')->with('message', 'Community aggiornata con successo!');
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

        {{-- Logo con Anteprima --}}
        <div class="mb-3">
            <label for="logo" class="block text-sm font-medium mb-[-5px]">logo</label>

            <div class="flex items-center space-x-4">
                {{-- Bottone Personalizzato --}}
                <div class="flex-1">
                    <label for="logo"
                        class="input-et flex items-center justify-center cursor-pointer hover:border-gray-400 transition-colors">
                        <span class="text-gray-400">
                            {{ $logo ? 'Cambia immagine' : 'Seleziona un file' }}
                        </span>

                        {{-- Input REALE nascosto --}}
                        <input type="file" id="logo" wire:model="logo" class="hidden" accept="image/*" />
                    </label>
                </div>

                {{-- Anteprima --}}
                <div class="shrink-0">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}"
                            class="size-16 rounded-full object-cover border border-gray-600">
                    @elseif ($community->logo_img)
                        <img src="{{ $community->logo_img }}"
                            class="size-16 rounded-full object-cover border border-gray-600">
                    @else
                        <div
                            class="size-16 rounded-full border border-dashed border-gray-600 flex items-center justify-center text-[10px] text-gray-500 text-center">
                            no logo
                        </div>
                    @endif
                </div>
            </div>

            {{-- Indicatore di caricamento --}}
            <div wire:loading wire:target="logo" class="text-xs text-cyan mt-1">
                caricamento immagine...
            </div>

            @error('logo')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>salva</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>
