<template>
    <div class="flex pt-2 mb-5 w-full">
        <!-- <div class="h-28 w-22 bg-cover bg-center rounded-md flex-shrink-0" :style="{ backgroundImage: getPoster(event.attributes.poster) }">
            </div> -->
        <NuxtLink :to="'/events/' + event?.id" class="w-22 rounded-md flex-shrink-0">
            <!--- h-28??? -->
            <img :src="getPoster(event?.attributes.poster)" alt="" class="rounded-md" />
        </NuxtLink>

        <div class="flex flex-col justify-between pl-4 w-full">
            <NuxtLink :to="'/events/' + event?.id">
                <div class="flex flex-col">
                    <span class="text-[9px] text-white opacity-70">{{ $t('event.type.' + event?.attributes.type) }}</span>
                    <h3 class="text-pink font-anta leading-[18px]" v-text="event?.attributes.title"></h3>
                </div>
            </NuxtLink>
            <div class="">
                <div class="flex items-center">
                    <img src="/icons/calendar-cyan.svg" alt="" class="!w-3.5 mr-2" />
                    <span class="text-white font-anta text-sm">{{ formatDate(event?.attributes.startDate) }}</span>
                </div>
                <div class="flex justify-between">
                    <div class="flex items-center">
                        <img src="/icons/location-cyan.svg" alt="" class="!w-3.5 mr-2" />
                        <span class="text-white font-anta text-sm"
                            >{{ event?.relationships.address.attributes.city.name }}, {{ event?.relationships.address.attributes.province.code }}</span
                        >
                    </div>
                    <LeftBookmark :eventId="event?.id" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const props = defineProps({
    event: Object,
})
</script>
