<?php

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Active = 'activated';
    case Terminate = 'terminated';
    case Rejected = 'rejected';
}
