<?php

namespace App\Livewire;

use DrPshtiwan\LivewireAsyncSelect\Livewire\AsyncSelect as BaseAsyncSelect;

class Location extends BaseAsyncSelect
{
    // Override methods or add new ones
    public function render()
    {
        return view('livewire.location');
    }
}
