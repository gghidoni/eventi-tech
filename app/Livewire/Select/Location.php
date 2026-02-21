<?php

namespace App\Livewire\Select;

use DrPshtiwan\LivewireAsyncSelect\Livewire\AsyncSelect as BaseAsyncSelect;
use Illuminate\Contracts\View\View;

class Location extends BaseAsyncSelect
{
    // Override methods or add new ones
    public function render(): View
    {
        return view('livewire.select.location');
    }
}
