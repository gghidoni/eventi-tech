<template>
    <div class="mt-9" ref="searchContainer">
        <div class="relative max-w-sm">
            <input type="text" placeholder="cerca un evento" class="input-et" @focus="openLocationSearch"
                v-model="search" />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
                <!-- Icona SVG (es. lente di ingrandimento) -->
                <img src="/icons/lente.svg" alt="">
            </div>
        </div>

        <transition name="fade-slide">
            <div v-if="searchOpen" class="relative max-w-sm mt-6">
                <input type="text" placeholder="scegli una località" class="input-et" />
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none mb-1">
                    <!-- Icona SVG (es. lente di ingrandimento) -->
                    <img src="/icons/location.svg" alt="">
                </div>
            </div>
        </transition>

        <Multiselect v-model="selectedOptions" id="ajax" track-by="id" label="attributes.title" :show-labels="false"
            :multiple="false" :searchable="true" :internal-search="false" :clear-on-select="false"
            :preserve-search="true" @search-change="handleAddressBookSearch" :options="test">
            <template #option="{ option }">
                <div class="option__desc">
                    <span class="option__title">{{ option.name }}</span>
                </div>
            </template>
            <template #singleLabel="{ option }">
                <div class="option__desc">
                    <span class="option__title">{{ option.name }}</span>
                </div>
            </template>
        </Multiselect>


        <p class="text-cyan mt-6 flex items-center underline text-sm">Scopri tutti gli eventi <img class="ml-3 w-3"
                src="/icons/right.svg" alt=""></p>


    </div>
</template>

<script setup>

import { ref, onMounted, onUnmounted } from 'vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

// Stato per contenere le opzioni selezionate 
const selectedOptions = ref(null)
const test = ref([])

const currentAddressBookQuery = ref('')
const debounceTimeout = ref(null)

const handleAddressBookSearch = (query) => {
    currentAddressBookQuery.value = query
    if (debounceTimeout.value) clearTimeout(debounceTimeout.value)
    debounceTimeout.value = setTimeout(() => {
        if (currentAddressBookQuery.value.length > 2) {
            asyncFind(query)
        }
    }, 2000)
}


const asyncFind = async (query) => {
    try {
        const response = await $apiFetch('/address_book?query=' + query, {
            method: 'GET'
        })


        test.value = response.data
        loading.value = false;

        console.log(response);
    } catch (err) {
        console.error('Login failed:', err)
    }

}

const searchOpen = ref(false);
const searchContainer = ref(null);
const { $apiFetch } = useNuxtApp()

const search = defineModel('modelValue')

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
