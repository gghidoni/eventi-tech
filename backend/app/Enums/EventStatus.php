<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Active = 'activated';
    case Terminated = 'terminated';
    case Rejected = 'rejected';
}
