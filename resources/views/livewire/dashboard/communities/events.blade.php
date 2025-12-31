<?php

use Livewire\Volt\Component;

new class extends Component {

    public $selectedCommunity = null;
    public int $preselectedCommunity;
    public $communities;

    public function rendering($view)
    {
        $view->layout('components.layouts.base', ['title' => __('I miei eventi')]);
    }

    public function mount()
    {
        $this->communities = auth()->user()->communities->map(fn($community) => [
            'value' => (string)$community->id,
            'label' => $community->name,
            'image' => $community->logo_img
        ])->toArray();

        $this->preselectedCommunity = $this->communities[0]['value'];
    }
}; ?>

<div class="page">
    <div style="relative w-100% mt-4 text-gray-300">
        <livewire:select.communities 
            name="selectedCommunity" 
            wire:model.live="selectedCommunity" 
            :value="$preselectedCommunity" 
            :options="$communities"
            placeholder="scegli una community" 
            :searchable="false"
        />
    </div>
</div>
