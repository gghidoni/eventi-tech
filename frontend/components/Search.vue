<template>
    <div class="mt-9" ref="searchContainer">
        <div class="relative max-w-sm">
            <input type="text" placeholder="cerca un evento" class="input-et" @focus="openLocationSearch" />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
                <!-- Icona SVG (es. lente di ingrandimento) -->
                <img src="/icons/lente.svg" alt="">
            </div>
        </div>

        <transition name="fade-slide" >
            <div v-if="searchOpen" class="relative max-w-sm mt-6">
                <input type="text" placeholder="scegli una località" class="input-et" />
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none mb-1">
                    <!-- Icona SVG (es. lente di ingrandimento) -->
                    <img src="/icons/location.svg" alt="">
                </div>
            </div>
        </transition>


        <p class="text-cyan mt-6 flex items-center underline">Scopri tutti gli eventi <img class="ml-3 w-4"
                src="/icons/right.svg" alt=""></p>


    </div>
</template>

<script setup>

import { ref, onMounted, onUnmounted } from 'vue'
const searchOpen = ref(false);
const searchContainer = ref(null);

function openLocationSearch() {
    searchOpen.value = true;
    console.log(searchOpen.value);
}

const searchClose = () => {
    searchOpen.value = false;
    console.log('Click fuori dal div!');
};

// Gestione click outside
const handleClickOutside = (event) => {
    if (searchContainer.value && !searchContainer.value.contains(event.target)) {
        searchClose();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
