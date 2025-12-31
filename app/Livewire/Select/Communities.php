<?php

namespace App\Livewire\Select;

use DrPshtiwan\LivewireAsyncSelect\Livewire\AsyncSelect as BaseAsyncSelect;

class Communities extends BaseAsyncSelect
{
    // Override methods or add new ones
    public function render()
    {
        return view('livewire.select.communities');
    }
}
