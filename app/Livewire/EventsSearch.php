<?php

namespace App\Livewire;

use App\Models\Event;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;
use Log;

class EventsSearch extends Component
{
    use WithPagination;

    public string $query = '';

    public $location = null;

    public function updatedQuery(): void
    {
        Log::info('Query updated:', ['query' => $this->query]);
        $this->resetPage();
    }

    public function updatedLocation($location): void
    {
        Log::info('Location selected:', ['location' => $location]);
        $this->resetPage();
    }

    public function render()
    {
        $events = Event::query();

        if ($this->location) {
            $locationData = json_decode($this->location, true);
            $field = match ($locationData['type']) {
                'comune'    => 'city_id',
                'provincia' => 'province_id',
                'regione'   => 'region_id',
                default     => throw new Exception('Tipo di location non valido'),
            };
            $events = $events->whereHas('address_book', function ($q) use ($field, $locationData) {
                $q->where($field, $locationData['id']);
            });
        }

        $searchIds = [];
        if (mb_strlen($this->query) > 2) {
            $searchIds = Event::search($this->query)->keys()->toArray();
            $events = $events->whereIn('id', $searchIds);
        }

        $events = $events->with(['address_book.city', 'address_book.province', 'community']);

        if (auth()->check()) {
            $events->withCount(['bookmarks as is_bookmarked' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        $events = $events->paginate(8);

        return view('livewire.events-search', [
            'events' => $events,
        ]);
    }
}
