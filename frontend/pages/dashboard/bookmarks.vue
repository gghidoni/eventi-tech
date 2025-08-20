<template>
    <div class="page">
        <Title>I miei eventi preferiti</Title>
        <h1 class="page-title">I miei eventi preferiti</h1>
        <div class="mt-6" v-if="isAuthenticated">
            pippo
        </div>
        <div v-else class="mt-6">
            <p>fai <NuxtLink class="text-cyan" to="/login">login</NuxtLink> o <NuxtLink class="text-cyan" to="/register">registrati</NuxtLink> per tenere traccia dei tuoi eventi preferiti</p>
        </div>
    </div>
</template>

<script setup>
const { isAuthenticated, user, logout } = useAuth()
</script>