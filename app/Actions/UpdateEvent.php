<?php

namespace App\Actions;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class UpdateEvent
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data): Event {
            $tagIds = $data['tag_ids'] ?? null;

            unset($data['tag_ids']);

            $event->update($data);

            // Se i tag sono presenti nel payload, sincronizziamo la pivot.
            if (is_array($tagIds)) {
                $event->tags()->sync(
                    collect($tagIds)
                        ->map(fn ($id): int => (int) $id)
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
