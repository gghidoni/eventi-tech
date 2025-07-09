<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <h1 class="basis-full text-3xl text-gray-300">scopri, <span class="text-cyan">partecipa</span>, connettiti!</h1>
        <Search />

        <div class="mt-10">
            <div v-if="loading" class="text-gray-400">Caricamento...</div>
            <div v-else-if="events.length == 0">
                <span>Nessun evento trovato...</span>
            </div>
            <div v-else>
                <EventMiniCard v-for="event in events" :event="event" />

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

onMounted(() => {
    fetchEvents()
})

const fetchEvents = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await $apiFetch('/events?include=community', {
            method: 'GET'
        })

        events.value = response.data
        loading.value = false;



        console.log(response.data);
    } catch (err) {
        console.error('Login failed:', err)
    }
}




</script>