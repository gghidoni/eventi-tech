<?php

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Active = 'activated';
    case Terminated = 'terminated';
    case Rejected = 'rejected';
}
