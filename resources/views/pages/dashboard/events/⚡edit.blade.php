<?php

use Livewire\Component;
use App\Models\Event;
use App\Enums\EventType;
use App\Actions\UpdateEvent;
use App\Actions\ProcessPoster;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    public Event $event;
    public $poster;

    #[Validate(['required', 'string', 'max:100', 'min:6'])]
    public string $title = '';

    #[Validate(['required', 'string', 'max:1000', 'min:6'])]
    public string $description = '';

    #[Validate(['required'])]
    public $type;

    #[Validate(['required'])]
    public $start_date;

    #[Validate(['required'])]
    public $end_date;

    #[Validate(['sometimes', 'url'])]
    public string $website = '';

    #[Validate(['sometimes', 'url'])]
    public string $tickets_url = '';

    #[Validate(['sometimes', 'url'])]
    public string $cfp_url = '';

    public $types;

    public function mount(Event $event)
    {
        $this->event = $event;

        $this->fill($this->event->only(['title', 'description', 'website', 'tickets_url', 'cfp_url']));

        $this->start_date = $this->event->start_date?->format('d-m-Y H:i');
        $this->end_date = $this->event->end_date?->format('d-m-Y H:i');

        $this->type = $this->event->type;

        $this->types = array_column(EventType::cases(), 'value');
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('Modifica evento')]);
    }

    public function save(UpdateEvent $updateEventAction, ProcessPoster $processPosterAction)
    {
        $data = $this->validate();

        try {
            unset($data['poster']);

            $data['start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->start_date);
            $data['end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->end_date);

            if ($this->poster) {
                $processPoster = $processPosterAction->execute($this->poster);
                $data['poster'] = $processPoster['desktop'];
                $data['poster_mobile'] = $processPoster['mobile'];
                $data['poster_thumb'] = $processPoster['thumb'];
            }

            $updateEventAction->execute($this->event, $data);
            return redirect()->route('dashboard.communities.events')->with('success', 'Evento modificato con successo');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->route('dashboard.communities.events')->with('error', 'Errore durante la modifica dell\'evento');
        }
    }
}; ?>


<div class="page">
    <form wire:submit="save" class="mt-5">

        {{-- Title --}}
        <div class="mb-5">
            <label for="title" class="block text-sm font-medium mb-1 text-gray-500">titolo</label>
            <input type="text" id="title" name="title" class="input-et" wire:model="title" />
            @error('title')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Descrizione --}}
        <div class="mb-5">
            <label for="description" class="block text-sm font-medium mb-1 text-gray-500">descrizione</label>
            <textarea id="description" name="description" class="textarea-et !pt-1.5" wire:model="description" rows="6"></textarea>
            @error('description')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Tipo --}}
        <div class="mb-5">
            <label for="type" class="block text-sm font-medium mb-1 text-gray-500">tipo</label>
            <select id="type" name="type" class="input-et select-et" wire:model="type">
                @foreach ($types as $type)
                    <option value="{{ $type }}">
                        @lang('titles.event.type.' . $type)</option>
                @endforeach
            </select>
            @error('type')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Data inizio --}}
        <div class="mb-5" wire:ignore>
            <label class="block text-sm font-medium mb-1 text-gray-500">inizio</label>
            <div x-data="{
                init() {
                    flatpickr($refs.start, {
                        enableTime: true,
                        time_24hr: true,
                        dateFormat: 'd-m-Y H:i',
                        defaultDate: '{{ $start_date }}',
                        locale: 'it',
                        onChange: (selectedDates, dateStr) => {
                            $wire.set('start_date', dateStr);
                            // Avvisa l'altro input di aggiornare il minimo
                            $dispatch('start-date-changed', { date: dateStr });
                        }
                    })
                }
            }" class="relative">
                <input x-ref="start" type="text" wire:model="start_date" class="input-et w-full pl-10 !pr-4">
            </div>
        </div>

        {{-- Data fine --}}
        <div class="mb-5" wire:ignore>
            <label class="block text-sm font-medium mb-1 text-gray-500">fine</label>
            <div x-data="{
                picker: null,
                init() {
                    this.picker = flatpickr($refs.end, {
                        enableTime: true,
                        time_24hr: true,
                        dateFormat: 'd-m-Y H:i',
                        defaultDate: '{{ $end_date }}',
                        locale: 'it',
                        minDate: '{{ $start_date }}',
                        onChange: (selectedDates, dateStr) => {
                            $wire.set('end_date', dateStr);
                        }
                    });
                }
            }" @start-date-changed.window="picker.set('minDate', $event.detail.date)"
                class="relative">
                <input x-ref="end" type="text" wire:model="end_date" class="input-et w-full pl-10 !pr-4">
            </div>
        </div>

        {{-- Sito web --}}
        <div class="mb-5">
            <label for="website" class="block text-sm font-medium mb-1 text-gray-500">sito web</label>
            <input type="text" id="website" name="website" class="input-et" wire:model="website" />
            @error('website')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Tickets --}}
        <div class="mb-5">
            <label for="tickets_url" class="block text-sm font-medium mb-1 text-gray-500">tickets url</label>
            <input type="text" id="tickets_url" name="tickets_url" class="input-et" wire:model="tickets_url" />
            @error('tickets_url')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- CFP --}}
        <div class="mb-5">
            <label for="cfp_url" class="block text-sm font-medium mb-1 text-gray-500">cfp url</label>
            <input type="text" id="cfp_url" name="cfp_url" class="input-et" wire:model="cfp_url" />
            @error('cfp_url')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Poster con Anteprima --}}
        <div class="mb-5">
            <label for="poster" class="block text-sm font-medium mb-1 text-gray-500">locandina</label>

            <div class="flex items-center space-x-4 mb-4">
                {{-- Bottone Personalizzato --}}
                <div class="flex-1">
                    <label for="poster"
                        class="input-et flex items-center justify-center cursor-pointer hover:border-gray-400 transition-colors">
                        <span class="text-gray-400">
                            {{ $poster ? 'Cambia immagine' : 'Seleziona un file' }}
                        </span>

                        {{-- Input REALE nascosto --}}
                        <input type="file" id="poster" wire:model="poster" class="hidden" accept="image/*" />
                    </label>
                </div>
            </div>

            {{-- Anteprima --}}
            <div class="shrink-0">
                @if ($poster)
                    <img src="{{ $poster->temporaryUrl() }}"
                        class="h-40 rounded-sm object-cover border border-gray-600">
                @elseif ($event->poster_img)
                    <img src="{{ $event->poster_img }}" class="h-40 rounded-sm object-cover border border-gray-600">
                @else
                    <div
                        class="h-40 rounded-sm border border-dashed border-gray-600 flex items-center justify-center text-[10px] text-gray-500 text-center">
                        no locandina
                    </div>
                @endif
            </div>

            {{-- Indicatore di caricamento --}}
            <div wire:loading wire:target="poster" class="text-xs text-cyan mt-1">
                caricamento immagine...
            </div>

            @error('poster')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>salva</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>
