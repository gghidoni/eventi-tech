<?php

declare(strict_types=1);

namespace App\Livewire\Select;

use DrPshtiwan\LivewireAsyncSelect\Livewire\AsyncSelect as BaseAsyncSelect;
use Illuminate\Contracts\View\View;

class Tags extends BaseAsyncSelect
{
    public function render(): View
    {
        return view('livewire.select.tags');
    }
}
