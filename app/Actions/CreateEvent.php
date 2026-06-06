<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateEvent
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Event
    {
        return DB::transaction(function () use ($data): Event {
            $data['status'] = EventStatus::Pending->value;
            $tagIds = $data['tag_ids'] ?? [];

            unset($data['tag_ids']);

            /** @var Event $event */
            $event = Event::query()->create($data);

            // I tag vengono collegati dopo la creazione dell'evento.
            if (is_array($tagIds)) {
                $event->tags()->sync(
                    collect($tagIds)
                        ->filter(static fn (mixed $id): bool => is_int($id) || is_string($id))
                        ->map(static fn (int|string $id): int => (int) $id)
                        ->filter(fn (int $id): bool => $id > 0)
                        ->unique()
                        ->values()
                        ->all(),
                );
            }

            return $event;
        });
    }
}
