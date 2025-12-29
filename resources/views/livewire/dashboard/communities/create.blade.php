<?php

use Livewire\Volt\Component;

new class extends Component {
    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('Community')]);
    }
}; ?>

<div>
    //
</div>
