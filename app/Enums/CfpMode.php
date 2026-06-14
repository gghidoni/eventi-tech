<?php

declare(strict_types=1);

namespace App\Enums;

enum CfpMode: string
{
    case Internal = 'internal';
    case External = 'external';
}
