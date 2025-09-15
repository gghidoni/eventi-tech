<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CityResource;
use App\Http\Resources\V1\ProvinceResource;
use App\Http\Resources\V1\RegionResource;
use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddressBookController extends ApiController
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        $results = [];

        $cities = CityResource::collection(City::search($query)->take(5)->get())->resolve();
        Log::debug($cities);
        $provinces = ProvinceResource::collection(Province::search($query)->take(3)->get())->resolve();
        $regions = RegionResource::collection(Region::search($query)->take(2)->get())->resolve();

        if ($cities) {
            $results[] = $cities[0];
            array_shift($cities);
        }
        if ($provinces) {
            $results[] = $provinces[0];
            array_shift($provinces);
        }
        if ($regions) {
            $results[] = $regions[0];
            array_shift($regions);
        }

        if ($cities) {
            $results = array_merge($results, $cities);
        }
        if ($provinces) {
            $results = array_merge($results, $provinces);
        }
        if ($regions) {
            $results = array_merge($results, $regions);
        }

        return $this->success('Ricerca riuscita', $results);
    }
}
