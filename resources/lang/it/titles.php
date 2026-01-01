<?php

use App\Enums\CommunityStatus;
use App\Enums\EventType;

return [
    'event' => [
        'type' => [
            EventType::InPerson->value  => 'in presenza',
            EventType::Online->value    => 'online',
            EventType::Hybrid->value    => 'in presenza e online',
        ],
    ],
    'community' => [
        'status' => [
            CommunityStatus::Active->value   => 'attiva',
            CommunityStatus::Pending->value  => 'in sospeso',
            CommunityStatus::Rejected->value => 'rifiutata',
        ],
    ],
];
