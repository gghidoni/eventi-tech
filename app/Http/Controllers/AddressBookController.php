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
        $type = $request->string('type', 'all')->toString();
        $selected = $request->string('selected')->trim()->toString();
        $query = $request->string('search')->trim()->toString();

        if ($selected !== '') {
            return response()->json(['data' => $this->selectedLocationOptions($selected, $type)]);
        }

        if ($query === '') {
            return response()->json(['data' => []]);
        }

        $results = [];
        $cities = [];
        $provinces = [];
        $regions = [];

        if ($type === 'all') {
            $cities = City::query()
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($query).'%'])
                ->orderBy('name')
                ->limit(5)
                ->get()
                ->map(fn (City $city): array => $this->cityOption($city, false))
                ->toArray();
            $provinces = Province::query()
                ->whereRaw('LOWER(name) LIKE ? OR LOWER(code) LIKE ?', ['%'.mb_strtolower($query).'%', '%'.mb_strtolower($query).'%'])
                ->orderBy('name')
                ->limit(3)
                ->get()
                ->map(fn (Province $province): array => $this->provinceOption($province))
                ->toArray();
            $regions = Region::query()
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($query).'%'])
                ->orderBy('name')
                ->limit(2)
                ->get()
                ->map(fn (Region $region): array => $this->regionOption($region))
                ->toArray();
        } elseif ($type === 'city') {
            $cities = City::query()
                ->with(['province.region'])
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($query).'%'])
                ->orderBy('name')
                ->limit(5)
                ->get()
                ->map(fn (City $city): array => $this->cityOption($city, true))
                ->toArray();
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

    /**
     * @return array<int, array{value:string, label:string, id:int}>
     */
    private function selectedLocationOptions(string $selected, string $type): array
    {
        $decoded = json_decode($selected, true);

        if (!is_array($decoded) || !isset($decoded['type'], $decoded['id'])) {
            return [];
        }

        $id = (int) $decoded['id'];

        return match ($decoded['type']) {
            'comune' => City::query()
                ->with(['province.region'])
                ->whereKey($id)
                ->get()
                ->map(fn (City $city): array => $this->cityOption($city, $type === 'city'))
                ->values()
                ->all(),
            'provincia' => Province::query()
                ->whereKey($id)
                ->get()
                ->map(fn (Province $province): array => $this->provinceOption($province))
                ->values()
                ->all(),
            'regione' => Region::query()
                ->whereKey($id)
                ->get()
                ->map(fn (Region $region): array => $this->regionOption($region))
                ->values()
                ->all(),
            default => [],
        };
    }

    /**
     * @return array{value:string, label:string, id:int}
     */
    private function cityOption(City $city, bool $withProvinceInfo): array
    {
        $label = $withProvinceInfo
            ? $city->name.' ('.$city->province->code.'), '.$city->province->region->name
            : $city->name;

        return [
            'value' => (string) json_encode(['type' => 'comune', 'id' => $city->id, 'name' => $label]),
            'label' => $label,
            'id'    => $city->id,
        ];
    }

    /**
     * @return array{value:string, label:string, id:int}
     */
    private function provinceOption(Province $province): array
    {
        return [
            'value' => (string) json_encode(['type' => 'provincia', 'id' => $province->id, 'name' => $province->name]),
            'label' => $province->name,
            'id'    => $province->id,
        ];
    }

    /**
     * @return array{value:string, label:string, id:int}
     */
    private function regionOption(Region $region): array
    {
        return [
            'value' => (string) json_encode(['type' => 'regione', 'id' => $region->id, 'name' => $region->name]),
            'label' => $region->name,
            'id'    => $region->id,
        ];
    }
}
