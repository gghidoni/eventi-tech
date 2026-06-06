<?php

use App\Actions\CreateAddressBook;
use App\Actions\CreateEvent;
use App\Actions\ProcessPoster;
use App\Enums\EventType;
use App\Mail\AdminNewEventNotification;
use App\Mail\CreatedNewEvent;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $poster;

    // #[Validate(['required', 'string', 'max:100', 'min:6'])]
    public string $title = '';

    // #[Validate(['required', 'string', 'max:1000', 'min:6'])]
    public string $description = '';

    // #[Validate(['required', 'string'])]
    public $type;

    // #[Validate(['required'])]
    public $start_date;

    // #[Validate(['required'])]
    public $end_date;

    // #[Validate(['sometimes', 'url'])]
    public string $website = '';

    // #[Validate(['sometimes', 'url'])]
    public string $tickets_url = '';

    // #[Validate(['sometimes', 'url'])]
    public string $cfp_url = '';

    // #[Validate(['required', 'string', 'max:100'])]
    public string $address_line = '';

    // #[Validate(['required'])]
    public $city;

    public $types;

    public $selectedCommunity = null;

    public $communities;

    public array $selectedTags = [];

    public function rules()
    {
        return [
            'title'        => 'required|string|max:100|min:6',
            'description'  => 'required|string|max:1000|min:6',
            'type'         => 'required|string',
            'start_date'   => 'required',
            'end_date'     => 'required',
            'website'      => 'sometimes|nullable|url',
            'tickets_url'  => 'sometimes|nullable|url',
            'cfp_url'      => 'sometimes|nullable|url',
            'address_line' => $this->type !== EventType::Online->value ? 'required|string|max:100' : 'nullable',
            'city'         => $this->type !== EventType::Online->value ? 'required' : 'nullable',
            'selectedTags' => 'array|max:4',
            'selectedTags.*' => 'integer|exists:tags,id',
        ];
    }

    public function mount(Event $event)
    {
        $this->event = $event;

        $this->types = array_column(EventType::cases(), 'value');

        $this->type = $this->types[0];

        $this->communities = auth()
            ->user()
            ->communities->map(
                fn ($community) => [
                    'value' => (string) $community->id,
                    'label' => $community->name,
                    'image' => $community->logo_img,
                ],
            )
            ->toArray();

        $this->selectedCommunity = $this->communities[0]['value'];
    }

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('dashboard.events.edit_title')]);
    }

    public function save(CreateEvent $createEventAction, ProcessPoster $processPosterAction, CreateAddressBook $createAddressBookAction)
    {
        $data = $this->validate();

        try {
            unset($data['poster']);

            $data['website'] = $data['website'] ?: null;
            $data['tickets_url'] = $data['tickets_url'] ?: null;
            $data['cfp_url'] = $data['cfp_url'] ?: null;

            $data['start_date'] = Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->start_date);
            $data['end_date'] = Carbon\Carbon::createFromFormat('d-m-Y H:i', $this->end_date);
            $data['community_id'] = (int) $this->selectedCommunity;
            $data['type'] = EventType::from($this->type);
            $data['tag_ids'] = collect($this->selectedTags)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->unique()
                ->take(4)
                ->values()
                ->all();

            if (EventType::from($this->type) !== EventType::Online) {
                $address['address_line'] = $this->address_line;
                $address['city_id'] = json_decode($this->city)->id;

                $addressBook = $createAddressBookAction->execute($address);

                $data['address_book_id'] = $addressBook->id;
            }

            if ($this->poster) {
                $processPoster = $processPosterAction->execute($this->poster);
                $data['poster'] = $processPoster['desktop'];
                $data['poster_mobile'] = $processPoster['mobile'];
                $data['poster_thumb'] = $processPoster['thumb'];
            }

            $event = $createEventAction->execute($data);

            $user = auth()->user();
            Mail::to($user)->send(new CreatedNewEvent($event));

            // Invia notifica a tutti gli admin
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                Mail::to($admin)->send(new AdminNewEventNotification($event));
            }

            return redirect()->route('dashboard.communities.events')->with('success', __('dashboard.events.success_created'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $message = __('common.error');
            $this->dispatch('messageSent', message: $message, success: false);
        }
    }
}; ?>


<div class="page">
    <form wire:submit="save" class="mt-5">

        <div style="relative w-100% mt-4 text-gray-300">
            <livewire:select.communities name="selectedCommunity" wire:model.live="selectedCommunity" :options="$communities"
                placeholder="{{ __('dashboard.events.select_community') }}" :searchable="false" />
        </div>

        {{-- Title --}}
        <div class="mb-5 mt-5">
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
            <select id="type" name="type" class="input-et select-et" wire:model.live="type">
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

        @if ($this->type != EventType::Online->value)
            {{-- Address --}}
            <div class="mb-5">
                <label for="address_line" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.address') }}</label>
                <input type="text" id="address_line" name="address_line" class="input-et"
                    wire:model="address_line" />
                @error('address_line')
                    <span class="text-pink text-xs">{{ $message }}</span>
                @enderror
            </div>

            <div style="mb-5">
                <label for="website" class="block text-sm font-medium text-gray-500 mb-[-12px]">{{ __('dashboard.events.fields.city') }}</label>
                <livewire:select.location name="city" wire:model="city" :endpoint="route('find')"
                    placeholder="{{ __('dashboard.events.fields.city_placeholder') }}" :extra-params="['type' => 'city']" />
                @error('city')
                    <span class="text-pink text-xs">{{ $message }}</span>
                @enderror
            </div>
        @endif

        {{-- Sito web --}}
        <div class="mb-5 mt-5">
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

        {{-- CFP --}}
        <div class="mb-5">
            <label for="cfp_url" class="block text-sm font-medium mb-1 text-gray-500">{{ __('dashboard.events.fields.cfp_url') }}</label>
            <input type="text" id="cfp_url" name="cfp_url" class="input-et" wire:model="cfp_url" />
            @error('cfp_url')
                <span class="text-pink text-xs">{{ $message }}</span>
            @enderror
        </div>

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
