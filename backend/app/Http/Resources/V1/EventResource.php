<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'event',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'type' => $this->type,
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'startDate' => $this->start_date,
                'endDate' => $this->end_date,
                'website' => $this->website,
                'poster' => $this->poster,
                'ticketsUrl' => $this->tickets_url,
                'cfpUrl' => $this->cfp_url
            ],
            'relationships' => [
                'community' => new CommunityResource($this->community),
                'address' => new AddressBookResource( $this->address_book)
            ],
            // 'relationships' => [
            //     'community' => [
            //         'data' => [
            //             'type' => 'community',
            //             'id' => $this->community_id,
            //             'name' => $this->community->name
            //         ]
            //     ]
            // ],
            // 'includes' => [
            //     'community' => new CommunityResource($this->whenLoaded('community')),
            //     'address' => new AddressBookResource( $this->whenLoaded('address_book'))
            // ],
        ];
    }
}
