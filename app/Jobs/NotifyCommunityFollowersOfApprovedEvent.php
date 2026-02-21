<?php

namespace App\Jobs;

use App\Enums\EventStatus;
use App\Mail\CommunityFavoriteEventApproved;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyCommunityFollowersOfApprovedEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $eventId) {}

    public function handle(): void
    {
        $event = Event::query()
            ->with('community')
            ->find($this->eventId);

        if (!$event instanceof Event || $event->status !== EventStatus::Active || !$event->community) {
            return;
        }

        // Invia la notifica solo agli utenti che seguono la community dell'evento.
        User::query()
            ->whereHas('favoriteCommunities', function (Builder $query) use ($event): void {
                $query->where('communities.id', $event->community->id);
            })
            ->chunkById(100, function ($users) use ($event): void {
                foreach ($users as $user) {
                    Mail::to($user)->send(new CommunityFavoriteEventApproved($event));
                }
            });
    }
}
