<template>
    <ul class="space-y-6 text-xl">
        <li>
            <NuxtLink to="/" @click="closeMenu">home</NuxtLink>
        </li>
        <li>
            <NuxtLink to="/about" @click="closeMenu">eventi</NuxtLink>
        </li>
        <li>
            <NuxtLink to="/contact" @click="closeMenu">contatti</NuxtLink>
        </li>
    </ul>
</template>

<script setup>
import { ref } from 'vue'


</script>
