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
            $cfpData = $data['cfp'] ?? null;

            unset($data['tag_ids']);
            unset($data['cfp']);

            /** @var Event $event */
            $event = Event::query()->create($data);

            if (is_array($cfpData)) {
                /** @var array<string, mixed> $cfpData */
                (new SaveEventCfp())->execute($event, $cfpData);
            }

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
