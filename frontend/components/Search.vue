<template>
    <div class="mt-6" ref="searchContainer">
        <div class="relative max-w-sm text-gray-300">
            <input type="text" placeholder="titolo, argomento..." class="input-et" v-model="search" />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mb-1">
                <img src="/icons/lente.svg" alt="" class="w-5">
            </div>
        </div>


        <div class="relative max-w-sm mt-4">
            <Multiselect v-model="selectedLocation" id="ajax" track-by="id" label="attributes.title"
                :show-labels="false" :multiple="false" :searchable="true" :internal-search="false"
                :clear-on-select="false" :preserve-search="true" @search-change="handleAddressBookSearch"
                :options="locations" placeholder="dove?">
                <template #caret>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none ">
                        <img src="/icons/location.svg" alt="" class="w-3.5">
                    </div>
                </template>
                <template #option="{ option }">
                    <div class="option__desc">
                        <span class="option__title font-sm">{{ option.attributes.name }} <b
                                class="text-pink">&#x2022;</b><b class="text-gray-400 font-thin text-xs option-type"> {{
                                    ' ' +
                                    $t('location.type.' + option.type) }}</b></span>
                    </div>
                </template>
                <template #singleLabel="{ option }">
                    <div class="option__desc bg-background">
                        <span class="option__title font-sm text-white">{{ option.attributes.name }} <b
                                class="text-pink">&#x2022;</b><b class="text-gray-400 font-thin text-xs option-type"> {{
                                    ' ' +
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

        <div class="relative max-w-sm mt-4 text-gray-300">
            <select class="appearance-none input-et pr-10 has-[option.placeholder:checked]:text-gray-500" name="" id="" v-model="selectedType">
                <option disabled selected value="" class="placeholder">in presenza?</option>
                <option value="online">online</option>
                <option value="in_person">in presenza</option>
                <option value="ibrido">ibrido</option>
                <option value="all">tutti</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mr-1">
                <img class="w-3.5" src="/icons/arrow-down.svg" alt="">
            </div>
        </div>





        <!-- <p class="text-cyan mt-6 flex items-center underline text-sm">Scopri tutti gli eventi <img class="ml-3 w-3"
                src="/icons/right.svg" alt=""></p> -->


    </div>
</template>

<script setup>

import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import { useI18n } from 'vue-i18n'


const locations = ref([])
const { t } = useI18n()

const currentAddressBookQuery = ref('')
const debounceTimeout = ref(null)

const { $apiFetch } = useNuxtApp()

const search = defineModel('search')
const selectedLocation = defineModel('selectedLocation')
const selectedType = defineModel('selectedType')

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





</script>
