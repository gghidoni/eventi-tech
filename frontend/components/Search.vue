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
            <div class="relative max-w-sm mt-6">
                <Multiselect v-model="selectedLocation" id="ajax" track-by="id" label="attributes.title"
                    :show-labels="false" :multiple="false" :searchable="true" :internal-search="false"
                    :clear-on-select="false" :preserve-search="true" @search-change="handleAddressBookSearch"
                    :options="locations" placeholder="dove?">
                    <template #caret>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none ">
                            <!-- Icona SVG (es. lente di ingrandimento) -->
                            <img src="/icons/location.svg" alt="">
                        </div>
                    </template>
                    <template #option="{ option }">
                        <div class="option__desc">
                            <span class="option__title font-sm">{{ option.attributes.name }} <b
                                    class="text-pink">&#x2022;</b><b
                                    class="text-gray-400 font-thin text-xs option-type"> {{ ' ' +
                                        $t('location.type.' + option.type) }}</b></span>
                        </div>
                    </template>
                    <template #singleLabel="{ option }">
                        <div class="option__desc bg-background">
                            <span class="option__title font-sm text-white">{{ option.attributes.name }} <b
                                    class="text-pink">&#x2022;</b><b
                                    class="text-gray-400 font-thin text-xs option-type"> {{ ' ' +
                                        $t('location.type.' + option.type) }}</b></span>
                        </div>
                    </template>
                    <template #noOptions>
                        <div class="p-2 text-gray-400 text-sm">
                            Città, province o regioni
                        </div>
                    </template>
                    <template #noResult>
                        <div class="p-2 text-gray-400 text-sm">
                            Nessun risultato trovato
                        </div>
                    </template>
                    <template #placeholder>
                        <span class="text-gray-500">dove?</span>
                    </template>
                </Multiselect>
            </div>
        </transition>




        <p class="text-cyan mt-6 flex items-center underline text-sm">Scopri tutti gli eventi <img class="ml-3 w-3"
                src="/icons/right.svg" alt=""></p>


    </div>
</template>

<script setup>

import { ref, onMounted, onUnmounted } from 'vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import { useI18n } from 'vue-i18n'


const locations = ref([])
const { t } = useI18n()

const currentAddressBookQuery = ref('')
const debounceTimeout = ref(null)

const searchOpen = ref(false);
const searchContainer = ref(null);
const { $apiFetch } = useNuxtApp()

const search = defineModel('search')
const selectedLocation = defineModel('selectedLocation')

const handleAddressBookSearch = (query) => {
    currentAddressBookQuery.value = query
    if (debounceTimeout.value) clearTimeout(debounceTimeout.value)
    debounceTimeout.value = setTimeout(() => {
        if (currentAddressBookQuery.value.length > 1) {
            asyncFind(query)
        } else if (currentAddressBookQuery.value.length == 0) {
            locations.value = []
        }
    }, 0)
}

const asyncFind = async (query) => {
    try {
        const response = await $apiFetch('/address_book?query=' + query, {
            method: 'GET'
        })
        locations.value = response.data
    } catch (err) {
        console.error('Login failed:', err)
    } 
}

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
