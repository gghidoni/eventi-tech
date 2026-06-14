<?php

use App\Enums\CfpSubmissionStatus;
use App\Models\CfpSubmission;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function rendering($view): void
    {
        $view->layout('components.layouts.base', ['title' => 'Le mie candidature']);
    }

    public function with(): array
    {
        return [
            'submissions' => CfpSubmission::query()
                ->with(['cfp.event.community'])
                ->where('user_id', auth()->id())
                ->whereNotIn('status', [
                    CfpSubmissionStatus::Draft->value,
                    CfpSubmissionStatus::Withdrawn->value,
                ])
                ->latest('submitted_at')
                ->paginate(8),
        ];
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
    <div class="mt-8">
        @forelse ($submissions as $submission)
            <div class="glass-card px-3 py-3 mb-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[10px] text-white opacity-70">{{ $submission->cfp->event->community->name }}</div>
                        <h2 class="font-anta leading-[18px] font-bold line-clamp-2">{{ $submission->title }}</h2>
                        <a href="{{ route('events.show', $submission->cfp->event) }}" class="block text-xs text-cyan underline mt-2" wire:navigate>
                            {{ $submission->cfp->event->title }}
                        </a>
                        <div class="text-xs text-gray-500 mt-1">
                            Inviata {{ $submission->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>
                    <span class="text-xs font-anta text-cyan whitespace-nowrap">{{ $this->statusLabel($submission->status) }}</span>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Non hai candidature attive.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $submissions->links('livewire.custom-pagination') }}
    </div>
</div>
