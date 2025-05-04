<template>
</template>

<script setup>
const { $apiFetch } = useNuxtApp();
const token = useCookie('token');
const user = useCookie('user');

onMounted(async () => {
    try {
        // Esegui la chiamata all'API di logout
        await $apiFetch('/logout', { method: 'POST' });

        // Rimuovi i cookie
        token.value = null;
        user.value = null;

        // Reindirizza alla home page
        navigateTo('/');
    } catch (error) {
        console.error('Logout fallito:', error);
        alert('Errore durante il logout');
    }
})



</script>