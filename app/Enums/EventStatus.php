<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Terminate = 'terminate';
    case Reject = 'reject';
}
