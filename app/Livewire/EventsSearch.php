<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;

class EventsSearch extends Component
{
    use WithPagination;

    public string $query = '';
    public $location = null;

    public function updatedQuery(): void
    {
        \Log::info('Query updated:', ['query' => $this->query]);
        $this->resetPage();
    }

    public function updatedLocation($location): void
    {
        \Log::info('Location selected:', ['location' => $location]);
        $this->resetPage();
    }

    public function render()
    {
        \Log::info('Render - query: ' . $this->query);
        \Log::info('Render - location: ' . $this->location);

        if (strlen($this->query) > 2) {
            $results = Event::search($this->query)->paginate(10);
        } else {
            $results = Event::query()->paginate(10);
        }

        return view('livewire.events-search', [
            'results' => $results,
        ]);
    }
}
