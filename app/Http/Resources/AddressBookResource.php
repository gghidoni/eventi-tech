<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AddressBook\AddressBook
 */
class AddressBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type'       => 'address_book',
            'id'         => $this->id,
            'attributes' => [
                'address_line' => $this->address_line,
                'city'         => [
                    'name' => $this->city->name,
                    'cap'  => $this->city->cap,
                ],
                'province' => [
                    'name' => $this->province->name,
                    'code' => $this->province->code,
                ],
                'region' => [
                    'name' => $this->region->name,
                ],
            ],
        ];
    }
}
