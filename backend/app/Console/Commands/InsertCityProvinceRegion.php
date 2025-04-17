<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
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
    public function handle()
    {
        $file = Storage::get('comuni.json');
        $items = json_decode($file, true);

        $this->output->progressStart(count($items));

        foreach ($items as $item) {
            if (!Region::whereName($item['denominazione_regione'])->exists()) {
                Region::create([
                    'name' => $item['denominazione_regione'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
            if (!Province::whereName($item['denominazione_provincia'])->exists()) {
                Province::create([
                    'name' => $item['denominazione_provincia'],
                    'code' => $item['sigla_provincia'],
                    'region_id' => Region::whereName($item['denominazione_regione'])->first()->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
            if (!City::whereName($item['denominazione_ita'])->exists()) {
                City::create([
                    'name' => $item['denominazione_ita'],
                    'cap' => $item['cap'],
                    'province_id' => Province::whereName($item['denominazione_provincia'])->first()->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('Comuni, province e regioni inseriti con successo!');
    }
}
