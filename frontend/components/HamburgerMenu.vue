<template>
    <div>
        <!-- Pulsante hamburger -->
        <div class="cursor-pointer z-50 relative w-10 h-10" @click="toggleMenu">
            <img src="/images/hamburger-menu.png" alt=""
                class="absolute top-1 left-0 w-11 transition-opacity duration-200 ease-in-out"
                :class="{ 'opacity-0': isOpen, 'opacity-100': !isOpen }">
            <img src="/images/hamburger-menu-closed.png" alt=""
                class="absolute top-1 left-0 w-8 transition-opacity duration-200 ease-in-out"
                :class="{ 'opacity-100': isOpen, 'opacity-0': !isOpen }">
        </div>

        <!-- Menu mobile -->
        <div v-if="isOpen"
            class="fixed top-[72px] left-0 w-full h-[calc(100vh-72px)] bg-[#2B2B2B] flex flex-col z-40 p-6">
            <ul v-if="!isDashboard" class="text-xl flex flex-col mt-6">
                <li>
                    <NuxtLink to="/" @click="closeMenu">home</NuxtLink>
                </li>
                <li class="mt-2">
                    <NuxtLink to="/about" @click="closeMenu">eventi</NuxtLink>
                </li>
                <li class="mt-2">
                    <NuxtLink to="/dashboard/bookmarks" @click="closeMenu">preferiti</NuxtLink>
                </li>
            </ul>
            <ul v-else class="flex flex-col space-y-2 mt-6">
                <li>
                    <NuxtLink class="flex space-x-2 items-center" to="/" @click="closeMenu"><img
                            src="/icons/home-white.svg" alt=""><span>home</span></NuxtLink>
                </li>
                <li>
                    <NuxtLink class="flex space-x-2 items-center" to="/dashboard/bookmarks" @click="closeMenu"><img
                            src="/icons/heart-white.svg" alt=""><span>preferiti</span></NuxtLink>
                </li>
                <li>
                    <NuxtLink class="flex space-x-2 items-center" to="/" @click="closeMenu"><img
                            src="/icons/users-white.svg" alt=""><span>community</span></NuxtLink>
                </li>
                <li>
                    <NuxtLink class="flex space-x-2 items-center" to="/" @click="closeMenu"><img
                            src="/icons/calendar-white.svg" alt=""><span>i miei eventi</span></NuxtLink>
                </li>
                <li>
                    <NuxtLink class="flex space-x-2 items-center" to="/" @click="closeMenu"><img
                            src="/icons/plus-white.svg" alt=""><span>nuovo evento</span></NuxtLink>
                </li>
            </ul>
            <!-- Menu per utenti autenticati -->
            <template v-if="isAuthenticated">
                <ul class="mt-9 text-sm">
                    <li class="flex space-x-2 items-center">
                        <img :src="getLogo(user.attributes.logo, user.attributes.name)" alt=""
                            class="rounded-full w-7 border border-cyan">
                        <span class="user-info text-pink">
                            {{ user.attributes.name }}
                        </span>
                    </li>
                    <li class="mt-2">
                        <button @click="handleLogout" class="logout-btn">logout</button>
                    </li>
                </ul>

                <!-- <li class="mt-2">
                        <NuxtLink to="http://127.0.0.1:8083/dashboard" target="_blank">
                            Vai al pannello Filament
                        </NuxtLink>
                    </li> -->
            </template>

            <!-- Menu per utenti non autenticati -->
            <template v-else>
                <ul class="mt-9 text-cyan">
                    <li class="mt-2">
                        <NuxtLink to="/login" @click="closeMenu">login</NuxtLink>
                    </li>
                    <li class="mt-2">
                        <NuxtLink to="/register" @click="closeMenu">registrati</NuxtLink>
                    </li>
                </ul>
            </template>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const { isAuthenticated, user, logout } = useAuth()
const route = useRoute()

const emit = defineEmits(['closeMenu'])

const handleLogout = () => {
    logout()
    closeMenu()
}

const isDashboard = computed(() => route.path.startsWith('/dashboard'))




const isOpen = ref(false)
const toggleMenu = () => {
    isOpen.value = !isOpen.value
}

const closeMenu = () => {
    isOpen.value = false;
}
</script>
