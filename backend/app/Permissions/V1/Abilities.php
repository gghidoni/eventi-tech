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

    public static function getAbilities(User $user)
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
        ];
    }
}
