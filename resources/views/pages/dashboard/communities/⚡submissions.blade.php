<?php

use App\Enums\CfpSubmissionStatus;
use App\Models\CfpSubmission;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $selectedEvent = '';

    public string $selectedStatus = CfpSubmissionStatus::Submitted->value;

    public function rendering($view): void
    {
        $view->layout('components.layouts.base', ['title' => 'Candidature']);
    }

    public function mount(): void
    {
        $requestedEvent = request()->query('event');
        $eventIds = $this->eventOptions()->pluck('id')->map(fn (int $id): string => (string) $id);

        if (is_string($requestedEvent) && $eventIds->contains($requestedEvent)) {
            $this->selectedEvent = $requestedEvent;

            return;
        }

        $this->selectedEvent = $eventIds->first() ?? '';
    }

    public function updatedSelectedEvent(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        if ($this->selectedStatus !== '' && !in_array($this->selectedStatus, $this->activeStatuses(), true)) {
            $this->selectedStatus = CfpSubmissionStatus::Submitted->value;
        }

        $this->resetPage();
    }

    public function with(): array
    {
        $submissions = CfpSubmission::query()
            ->with(['user', 'cfp.event.community'])
            ->whereNotIn('status', $this->inactiveStatuses())
            ->when($this->selectedStatus !== '', fn (Builder $query): Builder => $query->where('status', $this->selectedStatus))
            ->whereHas('cfp.event.community', fn (Builder $query): Builder => $query->where('user_id', auth()->id()))
            ->when($this->selectedEvent !== '', fn (Builder $query): Builder => $query->whereHas(
                'cfp.event',
                fn (Builder $eventQuery): Builder => $eventQuery->whereKey((int) $this->selectedEvent),
            ))
            ->latest('submitted_at')
            ->paginate(8);

        return [
            'events'      => $this->eventOptions(),
            'submissions' => $submissions,
            'statuses'    => $this->statusOptions(),
        ];
    }

    private function eventOptions()
    {
        return Event::query()
            ->whereHas('community', fn (Builder $query): Builder => $query->where('user_id', auth()->id()))
            ->whereHas('cfp.submissions', fn (Builder $query): Builder => $query->whereNotIn('status', $this->inactiveStatuses()))
            ->with('community')
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private function inactiveStatuses(): array
    {
        return [
            CfpSubmissionStatus::Draft->value,
            CfpSubmissionStatus::Withdrawn->value,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function activeStatuses(): array
    {
        return collect(CfpSubmissionStatus::cases())
            ->reject(fn (CfpSubmissionStatus $status): bool => in_array($status->value, $this->inactiveStatuses(), true))
            ->map(fn (CfpSubmissionStatus $status): string => $status->value)
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(CfpSubmissionStatus::cases())
            ->reject(fn (CfpSubmissionStatus $status): bool => in_array($status->value, $this->inactiveStatuses(), true))
            ->mapWithKeys(fn (CfpSubmissionStatus $status): array => [$status->value => $this->statusLabel($status)])
            ->all();
    }

    private function statusLabel(CfpSubmissionStatus $status): string
    {
        return match ($status) {
            CfpSubmissionStatus::Draft       => 'Bozza',
            CfpSubmissionStatus::Submitted   => 'Inviata',
            CfpSubmissionStatus::UnderReview => 'In review',
            CfpSubmissionStatus::Accepted    => 'Accettata',
            CfpSubmissionStatus::Rejected    => 'Rifiutata',
            CfpSubmissionStatus::Withdrawn   => 'Ritirata',
        };
    }
}; ?>

<div class="page">
    <div class="mt-4 grid md:grid-cols-2 text-gray-300" style="gap: 1.4375rem;">
        <div>
            <label for="selectedEvent" class="block text-sm font-medium mb-1 text-gray-500">Evento</label>
            <select id="selectedEvent" class="input-et select-et" wire:model.live="selectedEvent">
                @forelse ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                @empty
                    <option value="">Nessun evento con candidature attive</option>
                @endforelse
            </select>
        </div>

        <div>
            <label for="selectedStatus" class="block text-sm font-medium mb-1 text-gray-500">Stato</label>
            <select id="selectedStatus" class="input-et select-et" wire:model.live="selectedStatus">
                <option value="">Tutti gli stati attivi</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-8">
        @forelse ($submissions as $submission)
            <a href="{{ route('dashboard.cfps.submission', [$submission->cfp, $submission]) }}" class="block glass-card px-3 py-3 mb-5" wire:navigate>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[10px] text-white opacity-70">{{ $submission->cfp->event->title }}</div>
                        <h2 class="font-anta leading-[18px] font-bold line-clamp-2">{{ $submission->title }}</h2>
                        <div class="text-xs text-gray-400 mt-2">{{ $submission->user->name }} · {{ $submission->user->email }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Inviata {{ $submission->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>
                    <span class="text-xs font-anta text-cyan whitespace-nowrap">{{ $this->statusLabel($submission->status) }}</span>
                </div>
            </a>
        @empty
            <p class="text-gray-500 mt-6">Nessuna candidatura attiva.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $submissions->links('livewire.custom-pagination') }}
    </div>
</div>
