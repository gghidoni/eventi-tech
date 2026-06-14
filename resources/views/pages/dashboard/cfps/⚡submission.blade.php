<?php

use App\Actions\UpdateCfpSubmissionStatus;
use App\Enums\CfpSubmissionStatus;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use Livewire\Component;

new class extends Component
{
    public Cfp $cfp;

    public CfpSubmission $submission;

    public string $status = '';

    /** @var array<string, string> */
    public array $statuses = [];

    public function rendering($view): void
    {
        $view->layout('components.layouts.base', ['title' => 'Submission CFP']);
    }

    public function mount(Cfp $cfp, CfpSubmission $submission): void
    {
        $this->cfp = $cfp->load(['event.community', 'fields']);

        if ($this->cfp->event->community->user_id !== auth()->id()) {
            abort(403);
        }

        if ($submission->cfp_id !== $this->cfp->id) {
            abort(404);
        }

        $this->submission = $submission->load(['user', 'answers.field']);
        $this->status = $this->submission->status->value;
        $this->statuses = collect(CfpSubmissionStatus::cases())
            ->reject(fn (CfpSubmissionStatus $status): bool => $status === CfpSubmissionStatus::Draft)
            ->mapWithKeys(fn (CfpSubmissionStatus $status): array => [$status->value => $this->statusLabel($status)])
            ->all();
    }

    public function saveStatus(UpdateCfpSubmissionStatus $action): void
    {
        $this->validate([
            'status' => ['required', 'in:submitted,under_review,accepted,rejected,withdrawn'],
        ]);

        $action->execute($this->submission, CfpSubmissionStatus::from($this->status));
        $this->submission->refresh()->load(['user', 'answers.field']);
        session()->flash('success', 'Stato aggiornato.');
    }

    public function answerValue(int $fieldId): string
    {
        $answer = $this->submission->answers->firstWhere('cfp_template_field_id', $fieldId);
        $value = $answer?->value['value'] ?? null;

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_bool($value)) {
            return $value ? 'Sì' : 'No';
        }

        return $value === null || $value === '' ? '-' : (string) $value;
    }

    public function isFieldAddedAfterSubmission(int $fieldId): bool
    {
        $field = $this->cfp->fields->firstWhere('id', $fieldId);

        if (!$field || !$this->submission->submitted_at) {
            return false;
        }

        return $field->created_at->greaterThan($this->submission->submitted_at);
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

<div class="page space-y-6">
    <div>
        <a href="{{ route('dashboard.communities.submissions', ['event' => $cfp->event_id]) }}" class="text-cyan underline text-sm" wire:navigate>Candidature</a>
        <h1 class="text-2xl font-anta font-bold mt-3">{{ $submission->title }}</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $submission->user->name }} · {{ $submission->user->email }}</p>
    </div>

    @if (session('success'))
        <div class="border border-green rounded-sm p-3 text-sm text-green">
            {{ session('success') }}
        </div>
    @endif

    <section class="border border-gray-600 rounded-sm p-3 space-y-3">
        <div>
            <div class="text-xs text-gray-500">Abstract</div>
            <p class="text-sm mt-1">{{ $submission->abstract }}</p>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium mb-1 text-gray-500">Stato</label>
            <select id="status" class="input-et select-et" wire:model="status">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span class="text-pink text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="button" class="flex items-center space-x-2 text-cyan underline" wire:click="saveStatus">
            <span>Salva stato</span>
        </button>
    </section>

    <section class="border border-gray-600 rounded-sm p-3 space-y-4">
        <h2 class="font-anta text-lg">Risposte</h2>

        @forelse ($cfp->fields as $field)
            <div class="border-t border-gray-600 pt-3 first:border-t-0 first:pt-0">
                <div class="text-xs text-gray-500">{{ $field->label }}</div>
                <div class="text-sm mt-1">{{ $this->answerValue($field->id) }}</div>
                @if ($this->isFieldAddedAfterSubmission($field->id))
                    <div class="text-xs text-pink mt-1">Campo aggiunto dopo l'invio della candidatura.</div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">Nessun campo custom.</p>
        @endforelse
    </section>
</div>
