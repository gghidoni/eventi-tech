<template>
    <NuxtLink :to="'/events/' + event.id">
        <div class="flex pt-2 mb-5">
            <div class="h-28 w-22 bg-cover bg-center rounded-md flex-shrink-0" :style="{ backgroundImage: poster }">
            </div>
            <div class="flex flex-col justify-between pl-4">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white opacity-70">{{ $t('event.type.' + event.attributes.type)
                        }}</span>
                    <h3 class="text-pink font-anta leading-[18px]" v-text="event.attributes.title"></h3>

                </div>
                <div>
                    <div class="flex items-center">
                        <img src="/icons/calendar.svg" alt="" class="!w-3.5 mr-2">
                        <span class="text-white font-anta text-sm">{{ formatDate(event.attributes.startDate) }}</span>
                    </div>
                    <div class="flex items-center">
                        <img src="/icons/location.svg" alt="" class="!w-3.5 mr-2">
                        <span class="text-white font-anta text-sm">{{ event.includes.address.attributes.city.name }}, {{
                            event.includes.address.attributes.province.code }}</span>
                    </div>
                </div>

            </div>
        </div>
    </NuxtLink>
</template>

<script setup>
const props = defineProps({
    event: Object
})

const poster = computed(() => {
    const poster = props.event?.attributes?.poster
    return poster ? `url('${poster}')` : `url('/images/no-poster.png')`
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