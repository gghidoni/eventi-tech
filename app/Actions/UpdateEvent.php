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
            $shouldSyncCfp = array_key_exists('cfp', $data);
            $cfpData = $data['cfp'] ?? null;

            unset($data['tag_ids']);
            unset($data['cfp']);

            $event->update($data);

            if ($shouldSyncCfp) {
                if (is_array($cfpData)) {
                    /** @var array<string, mixed> $cfpData */
                    (new SaveEventCfp())->execute($event, $cfpData);
                } else {
                    $event->cfp()->delete();
                }
            }

            // Se i tag sono presenti nel payload, sincronizziamo la pivot.
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
