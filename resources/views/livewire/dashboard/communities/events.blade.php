<?php

use Livewire\Volt\Component;
use App\Models\Community;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $selectedCommunity = null;
    public $communities;

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('I miei eventi')]);
    }

    public function mount()
    {
        $this->communities = auth()
            ->user()
            ->communities->map(
                fn($community) => [
                    'value' => (string) $community->id,
                    'label' => $community->name,
                    'image' => $community->logo_img,
                ],
            )
            ->toArray();

        $this->selectedCommunity = $this->communities[0]['value'];
    }

    public function with(): array
    {
        $events = [];

        if ($this->selectedCommunity) {
            $events = Community::find($this->selectedCommunity)
                ->events()
                ->with(['address_book.city', 'address_book.province'])
                ->latest()
                ->paginate(8);
        }

        return [
            'events' => $events,
        ];
    }

    public function updatedSelectedCommunity()
    {
        $this->resetPage();
    }
}; ?>

<div class="page">
    <div style="relative w-100% mt-4 text-gray-300">
        <livewire:select.communities name="selectedCommunity" wire:model.live="selectedCommunity"
            :options="$communities" placeholder="scegli una community" :searchable="false" />
    </div>

    <div class="mt-8">
        @forelse($events as $event)
            <livewire:event-mini-card :event="$event" wire:key="{{$event->id}}" />
        @empty
            <p class="text-gray-500">Nessun evento trovato</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $events->links('livewire.custom-pagination') }}
    </div>
</div>
