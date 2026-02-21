<?php

namespace App\Livewire;

use App\Enums\EventStatus;
use App\Models\Event;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Log;

class EventsSearch extends Component
{
    use WithPagination;

    public string $query = '';

    public ?string $location = null;

    public function updatedQuery(): void
    {
        Log::info('Query updated:', ['query' => $this->query]);
        $this->resetPage();
    }

    public function updatedLocation(?string $location): void
    {
        Log::info('Location selected:', ['location' => $location]);
        $this->resetPage();
    }

    public function render(): View
    {
        $events = $this->getActiveEvents();

        if ($this->location) {
            $locationData = json_decode($this->location, true);

            if (
                !is_array($locationData)
                || !isset($locationData['type'], $locationData['id'])
            ) {
                throw new Exception('Formato location non valido');
            }

            $field = match ($locationData['type']) {
                'comune'    => 'city_id',
                'provincia' => 'province_id',
                'regione'   => 'region_id',
                default     => throw new Exception('Tipo di location non valido'),
            };
            $events = $events->whereHas('address_book', function (Builder $query) use ($field, $locationData): void {
                $query->where($field, $locationData['id']);
            });
        }

        $searchIds = [];
        if (mb_strlen($this->query) > 2) {
            $searchIds = Event::search($this->query)->keys()->toArray();
            $events = $events->whereIn('id', $searchIds);
        }

        $events = $events->with(['address_book.city', 'address_book.province', 'community']);

        if (auth()->check()) {
            $events->withCount(['bookmarks as is_bookmarked' => function (Builder $query): void {
                $query->where('user_id', auth()->id());
            }]);
        }

        $events = $events->paginate(8);

        return view('livewire.events-search', [
            'events' => $events,
        ]);
    }

    /**
     * @return Builder<Event>
     */
    private function getActiveEvents(): Builder
    {
        return Event::query()
            ->where('status', EventStatus::Active->value)
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'asc');
    }
}
