<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    #[On('bookmarkUpdated')]
    public function refreshBookmarks()
    {

    }

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
    <h1 class="text-xl">i miei eventi preferiti</h1>
    <div class="mt-8">
        @foreach ($events as $event)
            <livewire:event-mini-card :event="$event" wire:key="{{$event->id}}" />
        @endforeach
    </div>

    <div class="mt-4">
        {{ $events->links('livewire.custom-pagination') }}
    </div>

</div>
