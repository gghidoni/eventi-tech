<template>
    <div>
        <img v-if="!isBookmark && !loading" src="/icons/heart-pink-empty.svg" alt="" class="w-6" @click="toggleBookmark()">
        <img v-else src="/icons/heart-pink-fill.svg" alt="" class="w-6" @click="toggleBookmark()">
    </div>
</template>

<script setup>
import { computed } from 'vue'

const { isAuthenticated, user, token } = useAuth()
const { $apiFetch } = useNuxtApp()
const props = defineProps({
    eventId: {
        type: Number,
        required: true
    }
})
const loading = ref(false)

const toggleBookmark = async () => {
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

const isBookmark = computed(() => {
    if (!user.value || !user.value.relationships?.bookmarks) return false
    return user.value.relationships.bookmarks.includes(props.eventId)
})

</script>