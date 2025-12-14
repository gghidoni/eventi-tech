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
        \Log::info('=== START RENDER ===');
        \Log::info('Query: ' . $this->query);
        \Log::info('Location: ' . $this->location);

        $events = Event::query();

        if ($this->location) {
            $locationData = json_decode($this->location, true);
            match ($locationData['type']) {
                'comune' => $field = 'city_id',
                'provincia' => $field = 'province_id',
                'regione' => $field = 'region_id',
            };
            $events = $events->whereHas('address_book', function ($q) use ($field, $locationData) {
                $q->where($field, $locationData['id']);
            });
        }

        $searchIds = [];
        if (strlen($this->query) > 2) {
            $searchIds = Event::search($this->query)->get()->pluck('id')->toArray();
            \Log::info('Search IDs: ' . implode(',', $searchIds));
            $events = $events->whereIn('id', $searchIds);
            \Log::info('Events: ' . $events->get());
        }

        \Log::info('=== END RENDER ===');

        $events = $events->with('address_book.city', 'address_book.province')->get();

        return view('livewire.events-search', [
            'events' => $events,
        ]);
    }
}
