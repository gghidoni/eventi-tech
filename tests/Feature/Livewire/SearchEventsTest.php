<?php

use App\Livewire\SearchEvents;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(SearchEvents::class)
        ->assertStatus(200);
});
