<?php

namespace App\Permissions\V1;

use App\Models\User;

class Abilities
{
    public const CreateEvent = 'event:create';

    public const UpdateEvent = 'event:update';

    public const DeleteEvent = 'event:delete';

    public const UpdateOwnEvent = 'event:own:update';

    public const DeleteOwnEvent = 'event:own:delete';

    public const ToggleBookmark = 'bookmark:toggle';

    public const ShowOwnBookmarks = 'user:show-bookmarks';

    public static function getAbilities(User $user): array
    {
        if ($user->is_admin) {
            return [
                self::CreateEvent,
                self::DeleteEvent,
                self::UpdateEvent,
            ];
        }

        return [
            self::CreateEvent,
            self::DeleteOwnEvent,
            self::UpdateOwnEvent,
            self::ToggleBookmark,
            self::ShowOwnBookmarks,
        ];
    }
}
