<?php

use Livewire\Volt\Component;

new class extends Component {

    // Definisci il layout e il titolo qui
    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('Community')]);
    }

    public function with()
    {
        return [
            'communities' => auth()->user()->communities()->latest()->paginate(8), // 3. Pagina i risultati
        ];
    }
}; ?>

<div class="page">
    <h1 class="text-xl">gestisci le tue communities</h1>
    <div class="mt-6">
        <a class="flex items-center space-x-2 text-cyan" href="{{ route('dashboard.communities.create') }}" wire:navigate>
            <img src="/icons/plus-cyan.svg" alt="" class="w-4">
            <span>crea</span>
        </a>
    </div>
    @if (auth()->user()->communities()->count() == 0)
        <p class="text-gray-500 mt-8">crea la tua prima community per creare eventi</p>
    @else
        <div class="mt-8 flex flex-col space-y-2">
            @foreach ($communities as $community)
                <livewire:dashboard.community-card :community="$community" :key="$community->id" />
            @endforeach
        </div>

    @endif
</div>
