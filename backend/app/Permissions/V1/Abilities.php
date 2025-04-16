<?php

namespace App\Permissions\V1;

use App\Models\User;

class Abilities
{
    public const CreateTicket = 'ticket:create';

    public const UpdateTicket = 'ticket:update';

    public const DeleteTicket = 'ticket:delete';

    public const UpdateOwnTicket = 'ticket:own:update';

    public const DeleteOwnTicket = 'ticket:own:delete';

    public static function getAbilities(User $user)
    {
        if ($user->is_admin) {
            return [
                self::CreateTicket,
                self::DeleteTicket,
                self::UpdateTicket,
            ];
        } else {
            return [
                self::CreateTicket,
                self::DeleteOwnTicket,
                self::UpdateOwnTicket,
            ];
        }
    }
}
