<template>
    <div class="container mx-auto py-8 px-6 flex flex-col">
        <h1 class="basis-full text-3xl text-gray-300">scopri, <span class="text-cyan">partecipa</span>, connettiti!</h1>
        <Search />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Search from '../components/Search.vue'

const { $apiFetch } = useNuxtApp()





</script>