<template>
    <div>
        <img src="/icons/close-pink.svg" alt="" @click="leftBookmark()">
    </div>
</template>

<script setup>
import { computed } from 'vue'

const { isAuthenticated, user } = useAuth()
const { $apiFetch } = useNuxtApp()
const props = defineProps({
    eventId: {
        type: Number,
        required: true
    }
})
const loading = ref(false)

const leftBookmark = async () => {
    try {
        if (!isAuthenticated.value) return navigateTo('/login')
        const response = await $apiFetch('/events/' + props.eventId + '/toggle-bookmark/', {
            method: 'POST'
        })

        user.value = {
            ...user.value,
            relationships: {
                ...user.value.relationships,
                bookmarks: response.data
            }
        }

    } catch (e) {
        console.log(e)
    }
}

</script>