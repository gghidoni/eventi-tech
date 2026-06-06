<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InsertCityProvinceRegion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:insert-city-province-region';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $file = Storage::get('comuni.json');
        $items = json_decode((string) $file, true);

        if (!is_array($items)) {
            $this->error('Il file comuni.json non contiene un array valido.');

            return;
        }

        /** @var list<array{denominazione_regione:string, denominazione_provincia:string, sigla_provincia:string, denominazione_ita:string, cap:string}> $items */
        $this->output->progressStart(count($items));

        foreach ($items as $item) {
            $region = Region::firstOrCreate(
                ['name' => $item['denominazione_regione']],
                ['updated_at' => now(), 'created_at' => now()],
            );

            $province = Province::firstOrCreate(
                ['name' => $item['denominazione_provincia']],
                [
                    'code'       => $item['sigla_provincia'],
                    'region_id'  => $region->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            City::firstOrCreate(
                ['name' => $item['denominazione_ita']],
                [
                    'cap'         => $item['cap'],
                    'province_id' => $province->id,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ],
            );

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('Comuni, province e regioni inseriti con successo!');
    }
}
