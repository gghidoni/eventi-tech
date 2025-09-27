<template>
    <div class="page">
        <h1 class="basis-full text-2xl text-gray-300">scopri, <span class="text-cyan">partecipa</span>, connettiti!</h1>
        <Search v-model:search="query" v-model:selectedLocation="selectedLocation" v-model:selectedType="selectedType" />

        <div class="mt-8">
            <Spinner v-if="loading" />
            <div v-else-if="events.length == 0">
                <span class="text-white">Nessun evento trovato, prova ad ampliare i tuoi criteri di ricerca...</span>
            </div>
            <div v-else>
                <EventMiniCard v-for="event in events" :event="event" />

                <div v-if="pagination && pagination.lastPage > 1" class="flex gap-5 mt-6 justify-center">
                    <button :disabled="!pagination.links.prev" @click="goToPage(pagination.currentPage - 1)"
                        class="text-cyan mt-6 flex items-center underline text-sm">
                        <img class="mr-2 w-3" src="/icons/left-cyan.svg" alt="">
                        Precedente
                    </button>

                    <button :disabled="!pagination.links.next" @click="goToPage(pagination.currentPage + 1)"
                        class="text-cyan mt-6 flex items-center underline text-sm">
                        Successivo
                        <img class="ml-2 w-3" src="/icons/right-cyan.svg" alt="">
                    </button>
                </div>


            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import Search from '../components/Search.vue'
import Spinner from '../components/Spinner.vue'
import EventMiniCard from '~/components/EventMiniCard.vue'
import type { ApiResponse } from '~/types/api'
import type { Event } from '~/types/events'
import type { Pagination } from '~/types/pagination'

const loading = ref<boolean>(true)
const error = ref<string>('')
const events = ref<Event[]>([])
const { $apiFetch } = useNuxtApp()
const query = ref<string>('')
const selectedLocation = ref<any>(null)
const selectedType = ref<string>('')
let debounceTimeout: ReturnType<typeof setTimeout> | null = null
const pagination = ref<Pagination|null>({})

onMounted(() => {
    fetchEvents()
})

type Location = {
    field: string;
    id: number;
}

watch(query, (val: string) => {

    if (debounceTimeout) clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(() => {
        if (val.length > 2) {
            let location = getLocationQuery()
            search(val, location)
        } else {
            let location = getLocationQuery()
            if (location) {
                search(null, location)
            } else {
                fetchEvents()
            }

        }
    }, 500)
})

watch(selectedLocation, (val: Location) => {
    let location = getLocationQuery()
    search(query.value, location)
})

function getLocationQuery(): Location | null {
    if (selectedLocation.value) {
        return {
            field: selectedLocation.value.type + '_id',
            id: selectedLocation.value.id
        }
    }
    return null
}

const search = async (query: string | null = null, location: Location | null = null) => {
    let params = '';
    if (query) params += '&filter[search]=' + query;
    if (location) params += '&filter[location]=' + location.field + ',' + location.id;
    fetchEvents(params);
}

const goToPage = (page: number) => {
    const params = `&filter[search]=${query.value}&page=${page}`
    fetchEvents(params)
}


const fetchEvents = async (params = ''): Promise<void> => {
    loading.value = true;
    error.value = '';

    try {
        const response = await $apiFetch<ApiResponse<Event>>('/events?' + params, {
            method: 'GET'
        })

        pagination.value = {
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            links: response.links
        }

        events.value = response.data
        loading.value = false;
    } catch (err) {
        console.error('Login failed:', err)
    }
}




</script>