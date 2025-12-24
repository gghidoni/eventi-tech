<?php

declare(strict_types=1);

namespace App\Enums;

enum CommunityStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'reject';
}
