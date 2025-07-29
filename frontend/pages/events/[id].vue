<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <Spinner v-if="loading" />
        <div v-else-if="event.attributes">
            <h1 class="text-3xl font-anta text-cyan">{{ event.attributes.title }}</h1>
            <div class="flex justify-between pt-1">
                <div class="flex space-x-2 items-center">
                    <img src="/icons/calendar-pink.svg" alt="" class="w-4">
                    <span class="text-sm font-anta">{{ formatDate(event.attributes.startDate) }}</span>
                </div>
                <div class="flex space-x-2 items-center">
                    <img src="/icons/location-pink.svg" alt="" class="w-4">
                    <span class="text-white font-anta text-sm">{{ event.relationships.address.attributes.city.name }},
                        {{
                            event.relationships.address.attributes.province.code }}</span>
                </div>
            </div>
            <img :src="getPoster(event.attributes.poster)" alt="" class="h-90 rounded-md flex-shrink-0 mt-3">
            <div class="flex justify-between mt-3 items-center">
                <div class="flex items-center space-x-1.5">
                    <img :src="getLogo(event.relationships.community.attributes.logo, event.relationships.community.attributes.name)"
                        alt="" class="rounded-full w-7 border border-cyan">
                    <span class="text-sm font-anta">{{ event.relationships.community.attributes.name }}</span>
                </div>
                <div>
                    <img src="/icons/heart-pink-empty.svg" alt="" class="w-6">
                </div>
            </div>
            <div class="flex space-x-2 mt-3">
                <img src="/icons/clock-pink.svg" alt="" class="w-5">
                <span class="text-sm font-anta">{{ formatDateTime(event.attributes.startDate) }} - {{
                    formatDateTime(event.attributes.endDate) }}</span>
            </div>
            <div class="mt-5">
                <p class="text-sm font-thin">{{ event.attributes.description }}</p>
            </div>
            <div class="flex flex-col mt-6 space-y-2">
                <a v-if="event.attributes.ticketsUrl" :href="getUrl(event.attributes.ticketsUrl)" target="_blank" rel="noopener" class="flex space-x-2">
                    <img src="/icons/tickets-cyan.svg" alt="" class="w-4">
                    <span class="text-cyan text-sm underline">Biglietti</span>
                </a>
                <a v-if="event.attributes.cfpUrl" :href="getUrl(event.attributes.cfpUrl)" target="_blank" rel="noopener" class="flex space-x-2">
                    <img src="/icons/cfp-cyan.svg" alt="" class="w-4">
                    <span class="text-cyan text-sm underline">CFP</span>
                </a>
                <a href="/" class="flex space-x-2" target="_blank" rel="noopener"> 
                    <img src="/icons/add-calendar-cyan.svg" alt="" class="w-5">
                    <span class="text-cyan text-sm underline">Aggiungi al calendario</span>
                </a>
            </div>
        </div>
    </div>

</template>
<script setup>

import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Spinner from '../components/Spinner.vue'
const { $apiFetch } = useNuxtApp()



const route = useRoute()
const event = ref(Object)
const loading = ref(true)



onMounted(async () => {
    const id = route.params.id
    fetchEvent(id);
    console.log(id);
})

const fetchEvent = async (id) => {
    try {
        const response = await $apiFetch('/events/' + id)
        event.value = response.data
        loading.value = false
        console.log(event.value)
    } catch (error) {
        console.log(error)
    }
}

function getUrl(url) {
    if (url.includes('https://')) return url
    return 'https://' + url
}
</script>