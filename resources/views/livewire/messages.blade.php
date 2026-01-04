<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public string $message = '';
    public bool $show = false;
    public bool $success = true;

    #[On('messageSent')]
    public function showMessage(string $message, bool $success)
    {
        $this->message = $message;
        $this->success = $success;

        $this->show = true;
    }

    public function hide()
    {
        $this->show = false;
    }

    public function mount()
    {
        if(session()->has('success')) {
            $this->message = session()->get('success');
            $this->success = true;
            $this->show = true;
        }

        if(session()->has('error')) {
            $this->message = session()->get('error');
            $this->success = false;
            $this->show = true;
        }
    }
}; ?>

<div x-data="{
    t: null,
    arm() {
        clearTimeout(this.t);
        this.t = setTimeout(() => this.$wire.hide(), 4000);
    }
}" x-init="if ($wire.show) arm(); $watch('$wire.show', v => v ? arm() : clearTimeout(t))">
    <div x-cloak x-show="$wire.show" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed top-20 right-5 px-4 py-2 rounded shadow-lg z-50 text-sm" :class="$wire.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
        {{ $this->message }}
    </div>
</div>
