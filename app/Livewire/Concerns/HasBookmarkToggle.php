<?php

namespace App\Livewire\Concerns;

use App\Actions\ToggleBookmark;
use App\Models\Event;
use Auth;
use Throwable;

trait HasBookmarkToggle
{
    public bool $isBookmarked = false;

    public function toggleBookmark(ToggleBookmark $action)
    {
        // Utente non loggato
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Utente loggato ma NON verificato
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $this->authorize('bookmark', $this->event);

        try {
            $isBookmarked = $action->execute(auth()->user(), $this->event->id);
            $this->isBookmarked = $isBookmarked;

            // Chiude il menu se esiste (solo per event-mini-card)
            if (property_exists($this, 'menuOpen')) {
                $this->menuOpen = false;
            }

            if (!$this->isBookmarked) {
                $this->dispatch('bookmarkUpdated');
            }

            $message = $this->isBookmarked ? __('events.messages.bookmarked') : __('events.messages.unbookmarked');
            $this->dispatch('messageSent', message: $message, success: true);
        } catch (Throwable $th) {
            $message = __('common.error');
            $this->dispatch('messageSent', message: $message, success: false);
        }
    }

    protected function initializeBookmarkState(Event $event): void
    {
        if ($event->getAttribute('is_bookmarked') !== null) {
            $this->isBookmarked = (bool) $event->is_bookmarked;
        } else {
            $this->isBookmarked = auth()->check() && auth()->user()->bookmarks()->where('event_id', $event->id)->exists();
        }
    }
}
