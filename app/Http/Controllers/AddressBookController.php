<?php

namespace App\Http\Controllers;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressBookController extends Controller
{
    public function findLocation(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $query = $request->get('search');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $results = [];
        $cities = [];
        $provinces = [];
        $regions = [];

        if ($type === 'all') {
            $cities = City::search($query)->take(5)->get()->map(function ($city) {
                return [
                    'value' => json_encode(['type' => 'comune', 'id' => $city->id, 'name' => $city->name]),
                    'label' => $city->name,
                    'id'    => $city->id,
                ];
            })->toArray();
            $provinces = Province::search($query)->take(3)->get()->map(function ($province) {
                return [
                    'value' => json_encode(['type' => 'provincia', 'id' => $province->id, 'name' => $province->name]),
                    'label' => $province->name,
                    'id'    => $province->id,
                ];
            })->toArray();
            $regions = Region::search($query)->take(2)->get()->map(function ($region) {
                return [
                    'value' => json_encode(['type' => 'regione', 'id' => $region->id, 'name' => $region->name]),
                    'label' => $region->name,
                    'id'    => $region->id,
                ];
            })->toArray();
        } else if ($type === 'city') {
            $cities = City::search($query)->take(5)->get()->map(function ($city) {
                $label = $city->name . ' (' . $city->province->code . ')' . ', ' . $city->province->region->name;
                return [
                    'value' => json_encode(['type' => 'comune', 'id' => $city->id, 'name' => $label]),
                    'label' => $label,
                    'id'    => $city->id,
                ];
            })->toArray();
        }


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

        return response()->json(['data' => $results]);
    }
}
