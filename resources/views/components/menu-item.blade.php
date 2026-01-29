@props([
    'icon',
    'label',
    'url',
    'active' => false,
    'activeClass' => 'text-pink font-bold',
    'inactiveClass' => '',
])

<div class="flex space-x-2 items-center">
    <img src="{{ asset('icons/' . $icon . '.svg') }}" alt="" class="!w-4">
    <a href="{{ $url }}" wire:navigate {{ $attributes->class([$active ? $activeClass : $inactiveClass]) }}>{{ $label }}</a>
</div>
