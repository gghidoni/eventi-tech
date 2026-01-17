<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('dashboard.bookmarks.title')]);
    }

    #[On('bookmarkUpdated')]
    public function refreshBookmarks() {}

    public function with()
    {
        return [
            'events' => auth()
                ->user()
                ->bookmarks() // Assicurati che bookmarks sia una relazione (HasMany/BelongsToMany)
                ->latest()
                ->paginate(8), // 3. Pagina i risultati
        ];
    }
}; ?>

<div class="page">
    <h1 class="text-xl">{{ __('dashboard.bookmarks.heading') }}</h1>
    @if (count($events) == 0)
        <p class="text-gray-500 mt-8">{{ __('dashboard.bookmarks.empty') }}</p>
    @endif
    <div class="mt-8">
        @foreach ($events as $event)
            <livewire:event-mini-card :event="$event" wire:key="{{ $event->id }}" />
        @endforeach
    </div>

    <div class="mt-4">
        {{ $events->links('livewire.custom-pagination') }}
    </div>

</div>
