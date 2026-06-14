<?php

use Livewire\Component;
use App\Models\Event;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\EventType;
use App\Actions\UpdateEvent;
use App\Actions\ProcessPoster;
use App\Livewire\Concerns\ManagesEventCfpForm;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use ManagesEventCfpForm;

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

    public bool $has_cfp = false;

    public string $cfp_mode = CfpMode::External->value;

    public string $cfp_title = '';

    public string $cfp_description = '';

    public string $cfp_external_url = '';

    public $cfp_opens_at = '';

    public $cfp_closes_at = '';

    public string $cfp_status = CfpStatus::Published->value;

    public array $selectedTags = [];

    public $types;

    public array $cfpStatuses = [];

    public string $cfp_template_id = '';

    /** @var array<int, array<string, mixed>> */
    public array $cfp_fields = [];

    /** @var array<string, string> */
    public array $cfpFieldTypes = [];

    public function mount(Event $event)
    {
        $this->event = $event;

        if ($this->event->community->user_id !== auth()->id()) {
            abort(403);
        }

        $this->fill([
            'title'       => $this->event->title ?? '',
            'description' => $this->event->description ?? '',
            'website'     => $this->event->website ?? '',
            'tickets_url' => $this->event->tickets_url ?? '',
        ]);

        $this->start_date = $this->event->start_date?->format('d-m-Y H:i');
        $this->end_date = $this->event->end_date?->format('d-m-Y H:i');

        $this->type = $this->event->type;

        $this->types = array_column(EventType::cases(), 'value');
        $this->cfpStatuses = array_column(CfpStatus::cases(), 'value');
        $this->initializeCfpForm($this->event);

        $this->selectedTags = $this->event->tags()
            ->pluck('tags.id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('dashboard.events.edit_title')]);
    }

    public function save(UpdateEvent $updateEventAction, ProcessPoster $processPosterAction)
    {
        $data = $this->validate();
        validator(
            ['selectedTags' => $this->selectedTags],
            [
                'selectedTags' => 'array|max:4',
                'selectedTags.*' => 'integer|exists:tags,id',
            ],
        )->validate();
        $cfpPayload = $this->buildCfpPayload($this->title);

        try {
            unset($data['poster']);

            $data['website'] = $data['website'] ?: null;
            $data['tickets_url'] = $data['tickets_url'] ?: null;
            $data['tag_ids'] = collect($this->selectedTags)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->unique()
                ->take(4)
                ->values()
                ->all();

            $data['start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->start_date);
            $data['end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->end_date);

            if ($this->poster) {
                $processPoster = $processPosterAction->execute($this->poster);
                $data['poster'] = $processPoster['desktop'];
                $data['poster_mobile'] = $processPoster['mobile'];
                $data['poster_thumb'] = $processPoster['thumb'];
            }

            $data['cfp'] = $cfpPayload;

            $updateEventAction->execute($this->event, $data);
            return redirect()->route('dashboard.communities.events')->with('success', __('dashboard.events.success_updated'));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->route('dashboard.communities.events')->with('error', __('dashboard.events.error_update'));
        }
    }

}; ?>


<div class="page">
    <form wire:submit="save" class="mt-5">

        {{-- Title --}}
        <div class="mb-5">
            <label for="title" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.title') }}</label>
            <input type="text" id="title" name="title" class="input-et" wire:model="title" />
            @error('title')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Descrizione --}}
        <div class="mb-5">
            <label for="description" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.description') }}</label>
            <textarea id="description" name="description" class="textarea-et !pt-1.5" wire:model="description" rows="6"></textarea>
            @error('description')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Tipo --}}
        <div class="mb-5">
            <label for="type" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.type') }}</label>
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

        {{-- Tag --}}
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-500 mb-[-12px]">Tag</label>
            <livewire:select.tags name="selectedTags" wire:model.live="selectedTags" :endpoint="route('find.tags')"
                placeholder="Cerca tag (max 4)" :multiple="true" :max-selections="4" />
            @error('selectedTags')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
            @error('selectedTags.*')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Data inizio --}}
        <div class="mb-5" wire:ignore>
            <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.start') }}</label>
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
            <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.end') }}</label>
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
            <label for="website" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.website') }}</label>
            <input type="text" id="website" name="website" class="input-et" wire:model="website" />
            @error('website')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- Tickets --}}
        <div class="mb-5">
            <label for="tickets_url" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.tickets_url') }}</label>
            <input type="text" id="tickets_url" name="tickets_url" class="input-et" wire:model="tickets_url" />
            @error('tickets_url')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        @include('pages.dashboard.events.partials.cfp-form')

        {{-- Poster con Anteprima --}}
        <div class="mb-5">
            <label for="poster" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.poster') }}</label>

            <div class="flex items-center space-x-4 mb-4">
                {{-- Bottone Personalizzato --}}
                <div class="flex-1">
                    <label for="poster"
                        class="input-et flex items-center justify-center cursor-pointer hover:border-gray-400 transition-colors">
                        <span class="text-gray-400">
                            {{ $poster ? __('dashboard.events.fields.change_image') : __('dashboard.events.fields.select_file') }}
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
                        {{ __('dashboard.events.fields.no_poster') }}
                    </div>
                @endif
            </div>

            {{-- Indicatore di caricamento --}}
            <div wire:loading wire:target="poster" class="text-xs text-cyan mt-1">
                {{ __('dashboard.events.fields.uploading') }}
            </div>

            @error('poster')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>{{ __('common.actions.save') }}</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>
