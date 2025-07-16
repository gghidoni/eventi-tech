<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <h1 class="basis-full text-3xl text-gray-300">scopri, <span class="text-cyan">partecipa</span>, connettiti!</h1>
        <Search v-model="query" />

        <div class="mt-10">
            <div v-if="loading" class="text-gray-400">Caricamento...</div>
            <div v-else-if="events.length == 0">
                <span>Nessun evento trovato...</span>
            </div>
            <div v-else>
                <EventMiniCard v-for="event in events" :event="event" />

                <div v-if="pagination && pagination.lastPage > 1" class="flex gap-5 mt-6 justify-center">
                    <button :disabled="!pagination.links.prev" @click="goToPage(pagination.currentPage - 1)"
                        class="text-cyan mt-6 flex items-center underline text-sm">
                        <img class="mr-2 w-3" src="/icons/left.svg" alt="">
                        Precedente
                    </button>

                    <button :disabled="!pagination.links.next" @click="goToPage(pagination.currentPage + 1)"
                        class="text-cyan mt-6 flex items-center underline text-sm">
                        Successivo
                        <img class="ml-2 w-3" src="/icons/right.svg" alt="">
                    </button>
                </div>


            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Search from '../components/Search.vue'
import EventMiniCard from '~/components/EventMiniCard.vue'

const loading = ref(true)
const error = ref('')
const events = ref([])
const { $apiFetch } = useNuxtApp()
const query = ref('')
let debounceTimeout = null
const pagination = ref({})

onMounted(() => {
    fetchEvents()
})

watch(query, (val) => {

    if (debounceTimeout) clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(() => {
        if (val.length > 2) {
            search(val)
        } else {
            fetchEvents()
        }
    }, 1000)
})

const search = async (str) => {
    fetchEvents('&filter[search]=' + str);
}

const goToPage = (page) => {
    const params = `&filter[search]=${query.value}&page=${page}`
    fetchEvents(params)
}


const fetchEvents = async (params = '') => {
    loading.value = true;
    error.value = '';

    try {
        const response = await $apiFetch('/events?include=community,address_book' + params, {
            method: 'GET'
        })

        pagination.value = {
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            links: response.links
        }

        events.value = response.data
        loading.value = false;

        console.log(response);
    } catch (err) {
        console.error('Login failed:', err)
    }
}




</script>