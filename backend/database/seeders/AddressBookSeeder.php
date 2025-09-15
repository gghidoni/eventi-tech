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
            $region = Region::inRandomOrder()->first();

            // Selezione casuale di una provincia associata alla regione
            $province = Province::where('region_id', $region->id)->inRandomOrder()->first();

            // Selezione casuale di una città associata alla provincia
            $city = City::where('province_id', $province->id)->inRandomOrder()->first();

            // Creazione dell'address book
            AddressBook::create([
                'address_line' => 'Via '.fake()->streetName().' '.fake()->buildingNumber(), // Genera un indirizzo casuale
                'region_id'    => $region->id,
                'province_id'  => $province->id,
                'city_id'      => $city->id,
            ]);
        }
    }
}
