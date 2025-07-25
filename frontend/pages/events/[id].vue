<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <h1>ciao</h1>
    </div>
</template>
<script setup>

import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const { $apiFetch } = useNuxtApp()
const route = useRoute()



onMounted(async () => {
    try {
        const id = route.params.id
        console.log(id);
    } catch (error) {
        console.error('Errore nel caricamento evento:', error)
    }
})
</script>