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
            <ul class="text-xl flex flex-col mt-6">
                <li>
                    <NuxtLink to="/" @click="closeMenu">home</NuxtLink>
                </li>
                <li>
                    <NuxtLink to="/about" @click="closeMenu">eventi</NuxtLink>
                </li>
                <li>
                    <NuxtLink to="/contact" @click="closeMenu">contatti</NuxtLink>
                </li>
                <!-- Menu per utenti autenticati -->
                <template v-if="isAuthenticated">
                    <li>
                        <span class="user-info">
                            Ciao, {{ user.name || user.email }}
                        </span>
                    </li>
                    <li>
                        <button @click="handleLogout" class="logout-btn">Logout</button>
                    </li>
                </template>

                <!-- Menu per utenti non autenticati -->
                <template v-else>
                    <li>
                        <NuxtLink to="/login" @click="closeMenu">Login</NuxtLink>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const { isAuthenticated, user, logout } = useAuth()

const emit = defineEmits(['closeMenu'])

const handleLogout = () => {
  logout()
  closeMenu()
}

const isOpen = ref(false)
const toggleMenu = () => {
    isOpen.value = !isOpen.value
}

const closeMenu = () => {
    isOpen.value = false;
}
</script>
