<template>
    <div class="page">
        <Title>I miei eventi preferiti</Title>
        <h1 class="page-title">I miei eventi preferiti</h1>
        <div class="mt-6" v-if="isAuthenticated">

            <Spinner v-if="loading" />
            <div v-else-if="bookmarks.length == 0">
                <span class="text-white">Non hai ancora nessun evento nei preferiti</span>
            </div>
            <div v-else>
                <EventMiniCard v-for="event in bookmarks" :event="event" />
            </div>
        </div>
        <div v-else class="mt-6">
            <p>fai <NuxtLink class="text-cyan" to="/login">login</NuxtLink> o <NuxtLink class="text-cyan"
                    to="/register">registrati</NuxtLink> per tenere traccia dei tuoi eventi preferiti</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import EventMiniCard from '~/components/EventMiniCard.vue'
const { $apiFetch } = useNuxtApp()
const { isAuthenticated, user } = useAuth()
const bookmarks = ref([])
const loading = ref(false)

onMounted(() => {
    fetchBookmarks()
})

const fetchBookmarks = async () => {
    try {
        loading.value = true
        const response = await $apiFetch('users/bookmarks')
        console.log(response)
        bookmarks.value = response.data
        loading.value = false
    } catch (e) {
        console.log(e)
    }
}



</script>