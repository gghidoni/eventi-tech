<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Meilisearch\Client;

class AddressBookController extends ApiController
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        $results = [];

        $provinces = Province::search($query)->take(5)->get()->toArray();
        // Log::debug($provinces);

        $cities = City::search($query)->take(5)->get()->toArray();

        // Log::debug($cities);
        // Log::debug(array_shift($cities));



        $results[] = $cities[0];
        $results[] = $provinces[0];
        array_shift($cities);
        array_shift($provinces);


        $results = array_merge($results, $cities, $provinces);



        Log::debug($results);
    }
}
