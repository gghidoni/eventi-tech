<?php

namespace App\Livewire\Concerns;

use App\Actions\ToggleCommunityFavorite;
use App\Models\Community;
use Auth;
use Throwable;

trait HasCommunityFavoriteToggle
{
    public bool $isCommunityFavorited = false;

    public function toggleCommunityFavorite(ToggleCommunityFavorite $action)
    {
        // Utente non loggato.
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Utente loggato ma non verificato.
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        try {
            $isCommunityFavorited = $action->execute(auth()->user(), $this->community->id);
            $this->isCommunityFavorited = $isCommunityFavorited;

            // Chiude eventuale menu contestuale (riuso nel mini-card).
            if (property_exists($this, 'menuOpen')) {
                $this->menuOpen = false;
            }

            if (!$this->isCommunityFavorited) {
                $this->dispatch('communityFavoriteUpdated');
            }

            $message = $this->isCommunityFavorited
                ? __('communities.messages.favorited')
                : __('communities.messages.unfavorited');

            $this->dispatch('messageSent', message: $message, success: true);
        } catch (Throwable $th) {
            $this->dispatch('messageSent', message: __('common.error'), success: false);
        }
    }

    protected function initializeCommunityFavoriteState(Community $community): void
    {
        if ($community->getAttribute('is_favorited') !== null) {
            $this->isCommunityFavorited = (bool) $community->is_favorited;

            return;
        }

        $this->isCommunityFavorited = auth()->check()
            && auth()->user()->favoriteCommunities()->where('community_id', $community->id)->exists();
    }
}
