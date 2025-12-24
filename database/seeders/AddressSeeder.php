<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = Storage::get('comuni.json');
        $items = json_decode((string) $file, true);

        foreach ($items as $item) {
            if (!Region::whereName($item['denominazione_regione'])->exists()) {
                Region::query()->create([
                    'name'       => $item['denominazione_regione'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
            if (!Province::whereName($item['denominazione_provincia'])->exists()) {
                Province::query()->create([
                    'name'       => $item['denominazione_provincia'],
                    'code'       => $item['sigla_provincia'],
                    'region_id'  => Region::whereName($item['denominazione_regione'])->first()->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
            if (!City::whereName($item['denominazione_ita'])->exists()) {
                City::query()->create([
                    'name'        => $item['denominazione_ita'],
                    'cap'         => $item['cap'],
                    'province_id' => Province::whereName($item['denominazione_provincia'])->first()->id,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]);
            }

        }

    }
}
