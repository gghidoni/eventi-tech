<?php

use App\Actions\SubmitCfpApplication;
use App\Enums\CfpFieldType;
use App\Models\Cfp;
use App\Models\Event;
use Livewire\Component;

new class extends Component
{
    public Event $event;

    public Cfp $cfp;

    public string $title = '';

    public string $abstract = '';

    /** @var array<int|string, mixed> */
    public array $answers = [];

    public function rendering($view): void
    {
        $view->layout('components.layouts.base', ['title' => 'Candidatura CFP']);
    }

    public function mount(Event $event): void
    {
        $this->event = $event->load(['community', 'cfp.fields']);
        $cfp = $this->event->cfp;

        if (!$cfp) {
            abort(404);
        }

        $this->authorize('apply', $cfp);

        $this->cfp = $cfp;

        foreach ($this->cfp->fields as $field) {
            $this->answers[$field->id] = match ($field->type) {
                CfpFieldType::Checkbox    => false,
                CfpFieldType::Multiselect => [],
                default                   => '',
            };
        }
    }

    public function submit(SubmitCfpApplication $action)
    {
        $this->authorize('apply', $this->cfp);

        $action->execute(
            $this->cfp,
            auth()->user(),
            [
                'title'    => $this->title,
                'abstract' => $this->abstract,
            ],
            $this->answers,
        );

        session()->flash('success', 'Candidatura inviata.');

        return redirect()->route('events.show', $this->event);
    }

    /**
     * @return array<int, string>
     */
    public function fieldOptions(int $fieldId): array
    {
        $field = $this->cfp->fields->firstWhere('id', $fieldId);

        if (!$field || !is_array($field->options)) {
            return [];
        }

        return collect($field->options)
            ->filter(fn (mixed $option): bool => is_string($option) && mb_trim($option) !== '')
            ->map(fn (string $option): string => mb_trim($option))
            ->values()
            ->all();
    }
}; ?>

<div class="page space-y-6">
    <div>
        <a href="{{ route('events.show', $event) }}" class="text-cyan underline text-sm" wire:navigate>Evento</a>
        <h1 class="text-2xl font-anta font-bold mt-3">Candidati come speaker</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $cfp->title }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ $event->community->name }} · chiude {{ $cfp->closes_at->format('d/m/Y H:i') }}</p>
    </div>

    <form wire:submit="submit" class="space-y-5">
        <section class="border border-gray-600 rounded-sm p-3 space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium mb-1 text-gray-500">Titolo proposta</label>
                <input id="title" type="text" class="input-et" wire:model="title">
                @error('title') <span class="text-pink text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="abstract" class="block text-sm font-medium mb-1 text-gray-500">Abstract</label>
                <textarea id="abstract" rows="7" class="textarea-et" wire:model="abstract"></textarea>
                @error('abstract') <span class="text-pink text-xs">{{ $message }}</span> @enderror
            </div>
        </section>

        @foreach ($cfp->fields as $field)
            <section class="border border-gray-600 rounded-sm p-3 space-y-2" wire:key="cfp-answer-{{ $field->id }}">
                <label class="block text-sm font-medium text-gray-300">
                    {{ $field->label }}
                    @if ($field->required)
                        <span class="text-pink">*</span>
                    @endif
                </label>

                @if ($field->help_text)
                    <p class="text-xs text-gray-500">{{ $field->help_text }}</p>
                @endif

                @switch($field->type)
                    @case(CfpFieldType::Textarea)
                        <textarea rows="5" class="textarea-et" wire:model="answers.{{ $field->id }}" placeholder="{{ $field->placeholder }}"></textarea>
                        @break

                    @case(CfpFieldType::Select)
                        <select class="input-et select-et" wire:model="answers.{{ $field->id }}">
                            <option value="">Seleziona</option>
                            @foreach ($this->fieldOptions($field->id) as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @break

                    @case(CfpFieldType::Multiselect)
                        <div class="space-y-2">
                            @foreach ($this->fieldOptions($field->id) as $option)
                                <label class="flex items-center gap-2 text-sm text-gray-300">
                                    <input type="checkbox" class="rounded border-gray-600 bg-transparent" value="{{ $option }}" wire:model="answers.{{ $field->id }}">
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break

                    @case(CfpFieldType::Checkbox)
                        <label class="flex items-center gap-2 text-sm text-gray-300">
                            <input type="checkbox" class="rounded border-gray-600 bg-transparent" wire:model="answers.{{ $field->id }}">
                            <span>Sì</span>
                        </label>
                        @break

                    @case(CfpFieldType::Url)
                        <input type="url" class="input-et" wire:model="answers.{{ $field->id }}" placeholder="{{ $field->placeholder }}">
                        @break

                    @case(CfpFieldType::Email)
                        <input type="email" class="input-et" wire:model="answers.{{ $field->id }}" placeholder="{{ $field->placeholder }}">
                        @break

                    @case(CfpFieldType::Number)
                        <input type="number" class="input-et" wire:model="answers.{{ $field->id }}" placeholder="{{ $field->placeholder }}">
                        @break

                    @case(CfpFieldType::Date)
                        <input type="date" class="input-et" wire:model="answers.{{ $field->id }}">
                        @break

                    @default
                        <input type="text" class="input-et" wire:model="answers.{{ $field->id }}" placeholder="{{ $field->placeholder }}">
                @endswitch

                @error('answers.'.$field->id) <span class="text-pink text-xs">{{ $message }}</span> @enderror
            </section>
        @endforeach

        <button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
            <span>Invia candidatura</span>
            <img src="/icons/right-cyan.svg" alt="">
        </button>
    </form>
</div>
