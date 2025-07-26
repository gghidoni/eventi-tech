<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <Spinner v-if="loading" />
        <div v-else-if="event.attributes">
            <h1>{{ event.attributes.title }}</h1>
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
</script>