@if($url)
<div class="space-y-2">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</p>
    <img src="{{ $url }}" alt="Current image" class="rounded-lg border border-gray-300 dark:border-gray-600 max-h-64 object-contain bg-white dark:bg-gray-800" />
</div>
@endif
