<template>
    <div class="flex pt-2 mb-5">
        <div class="h-28 w-22 bg-cover bg-center rounded-md flex-shrink-0"
            :style="{ backgroundImage: `url('${(event.attributes.poster)}')` }"></div>
        <div class="flex flex-col pl-4">
            <h3 class="text-pink font-anta" v-text="event.attributes.title"></h3>
            <div class="pt-3 flex items-center">
                <img src="/icons/calendar.svg" alt="" class="!w-4 mr-2">
                <span class="text-white font-anta">{{ formatDate(event.attributes.startDate) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    event: Object
})

function formatDate(datetimeStr) {
    const date = new Date(datetimeStr);
    return date.toLocaleString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}
</script>