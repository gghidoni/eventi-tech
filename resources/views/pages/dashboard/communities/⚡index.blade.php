<?php

use Livewire\Component;

new class extends Component {

    // Definisci il layout e il titolo qui
    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('communities.title')]);
    }

    public function with()
    {
        return [
            'communities' => auth()->user()->communities()->latest()->paginate(8), // 3. Pagina i risultati
        ];
    }
}; ?>

<div class="page">
    <h1 class="text-xl">{{ __('communities.manage') }}</h1>
    <div class="mt-6">
        <a class="flex items-center space-x-2 text-cyan" href="{{ route('dashboard.communities.create') }}" wire:navigate>
            <img src="/icons/plus-cyan.svg" alt="" class="w-4">
            <span>{{ __('communities.create') }}</span>
        </a>
    </div>
    @if ($communities->isEmpty())
        <p class="text-gray-500 mt-8">{{ __('communities.first_community') }}</p>
    @else
        <div class="mt-8 flex flex-col space-y-2">
            @foreach ($communities as $community)
                <livewire:dashboard.communities.card :community="$community" :key="$community->id" />
            @endforeach
        </div>

    @endif
</div>
