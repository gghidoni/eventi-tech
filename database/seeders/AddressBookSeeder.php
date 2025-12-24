<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AddressBook\AddressBook;
use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Database\Seeder;

class AddressBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creazione di 4 address book
        for ($i = 0; $i < 4; $i++) {
            // Selezione casuale di una regione
            $region = Region::query()->inRandomOrder()->first();

            // Selezione casuale di una provincia associata alla regione
            $province = Province::query()->where('region_id', $region->id)->inRandomOrder()->first();

            // Selezione casuale di una città associata alla provincia
            $city = City::query()->where('province_id', $province->id)->inRandomOrder()->first();

            // Creazione dell'address book
            AddressBook::query()->create([
                'address_line' => 'Via '.fake()->streetName().' '.fake()->buildingNumber(), // Genera un indirizzo casuale
                'region_id'    => $region->id,
                'province_id'  => $province->id,
                'city_id'      => $city->id,
            ]);
        }
    }
}
