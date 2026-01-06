<?php

use App\Enums\CommunityStatus;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;

return [
    'event' => [
        'type' => [
            EventType::InPerson->value  => 'in presenza',
            EventType::Online->value    => 'online',
            EventType::Hybrid->value    => 'in presenza e online',
        ],
        'status' => [
            EventStatus::Pending->value => 'in sospeso',
            EventStatus::Active->value  => 'attivo',
            EventStatus::Reject->value => 'rifiutato',
            EventStatus::Terminate->value => 'terminato',
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
