<?php

declare(strict_types=1);

use App\Actions\CreateAddressBook;
use App\Models\AddressBook\AddressBook;
use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;

beforeEach(function () {
    $this->action = new CreateAddressBook();

    // Create a complete geographical hierarchy
    $this->region = Region::factory()->create(['name' => 'Lombardia']);
    $this->province = Province::factory()->forRegion($this->region)->create(['name' => 'Milano', 'code' => 'MI']);
    $this->city = City::factory()->forProvince($this->province)->create(['name' => 'Milano', 'cap' => '20100']);
});

test('creates address book from city id', function () {
    $data = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Roma 1',
    ];

    $addressBook = $this->action->execute($data);

    expect($addressBook)->toBeInstanceOf(AddressBook::class);
    expect($addressBook->city_id)->toBe($this->city->id);
    expect($addressBook->address_line)->toBe('Via Roma 1');
});

test('auto-populates province from city', function () {
    $data = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Milano 10',
    ];

    $addressBook = $this->action->execute($data);

    expect($addressBook->province_id)->toBe($this->province->id);
});

test('auto-populates region from city via province', function () {
    $data = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Test 123',
    ];

    $addressBook = $this->action->execute($data);

    expect($addressBook->region_id)->toBe($this->region->id);
});

test('creates address book with complete geographical hierarchy', function () {
    $data = [
        'city_id'      => $this->city->id,
        'address_line' => 'Piazza Duomo 1',
    ];

    $addressBook = $this->action->execute($data);

    expect($addressBook->city->name)->toBe('Milano');
    expect($addressBook->province->name)->toBe('Milano');
    expect($addressBook->region->name)->toBe('Lombardia');
});

test('saves address book to database', function () {
    $data = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Saved 1',
    ];

    $addressBook = $this->action->execute($data);

    $this->assertDatabaseHas('address_books', [
        'id'           => $addressBook->id,
        'city_id'      => $this->city->id,
        'province_id'  => $this->province->id,
        'region_id'    => $this->region->id,
        'address_line' => 'Via Saved 1',
    ]);
});

test('throws exception for non-existent city', function () {
    $data = [
        'city_id'      => 99999,
        'address_line' => 'Via Test 1',
    ];

    $this->action->execute($data);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('creates multiple address books for same city', function () {
    $data1 = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Prima 1',
    ];
    $data2 = [
        'city_id'      => $this->city->id,
        'address_line' => 'Via Seconda 2',
    ];

    $addressBook1 = $this->action->execute($data1);
    $addressBook2 = $this->action->execute($data2);

    expect($addressBook1->id)->not->toBe($addressBook2->id);
    expect($addressBook1->city_id)->toBe($addressBook2->city_id);
});
