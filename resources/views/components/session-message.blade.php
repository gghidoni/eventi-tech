<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:notify.window="
        message = $event.detail.message;
        type = $event.detail.type;
        show = true;
        setTimeout(() => show = false, 3000);
    "
    x-show="show"
    x-transition
    class="fixed top-20 right-5 px-4 py-2 rounded shadow-lg"
    :class="type === 'success'
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800'"
>
    <span x-text="message"></span>
</div>
