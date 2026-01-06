<?php

namespace App\Actions;

use App\Models\AddressBook\AddressBook;
use App\Models\AddressBook\City;
use Illuminate\Support\Facades\DB;
use Log;

class CreateAddressBook
{
    public function execute(array $data): AddressBook
    {
        return DB::transaction(function () use ($data) {
            $city = City::findOrFail($data['city_id']);
            Log::info($data);
            $adressBook = AddressBook::create([
                'address_line' => $data['address_line'],
                'city_id'      => $city->id,
                'province_id'  => $city->province->id,
                'region_id'    => $city->province->region->id,
            ]);

            return $adressBook;
        });
    }
}
