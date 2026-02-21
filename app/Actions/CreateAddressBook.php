<?php

namespace App\Actions;

use App\Models\AddressBook\AddressBook;
use App\Models\AddressBook\City;
use Illuminate\Support\Facades\DB;

class CreateAddressBook
{
    /**
     * @param  array{city_id:int|string, address_line?:string|null}  $data
     */
    public function execute(array $data): AddressBook
    {
        return DB::transaction(function () use ($data): AddressBook {
            $city = City::query()->with(['province.region'])->findOrFail((int) $data['city_id']);

            $addressBook = AddressBook::query()->create([
                'address_line' => $data['address_line'] ?? null,
                'city_id'      => $city->id,
                'province_id'  => $city->province->id,
                'region_id'    => $city->province->region->id,
            ]);

            return $addressBook;
        });
    }
}
