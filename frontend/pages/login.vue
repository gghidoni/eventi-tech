<template>
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">Accedi</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

            <UAlert v-if="error" color="error" variant="soft" :title="error"
                :close-button="{ icon: 'i-heroicons-x-mark-20-solid', color: 'gray', variant: 'link' }"
                @close="error = ''" class="mb-4" />


            <UForm :validate="validate" :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="email" name="email">
                    <UInput v-model="state.email" class="w-full" :ui="customInputUI" />
                </UFormField>

                <UFormField label="password" name="password">
                    <UInput v-model="state.password" :type="showPassword ? 'text' : 'password'" class="w-full"
                        :ui="customPasswordInputUI">
                        <template #trailing>
                            <UButton color="neutral" variant="link" size="sm"
                                :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword"
                                aria-controls="password" @click="showPassword = !showPassword" />
                        </template>
                    </UInput>
                </UFormField>

                <UButton type="submit" class="text-background mt-4">
                    login
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </UButton>
            </UForm>

        </div>
    </div>
</template>

<script setup lang="ts">

import type { FormError, FormSubmitEvent } from '@nuxt/ui'


const loading = ref(false)
const error = ref('')
const { customInputUI, customPasswordInputUI } = useCustomUI()

const { $apiFetch } = useNuxtApp()
const { token, user } = useAuth()

const state = reactive({
    email: undefined,
    password: undefined
})

const showPassword = ref(false)
const togglePassword = () => {
    console.log('sdsds');
    showPassword.value = !showPassword.value
}

const validate = (state: any): FormError[] => {
    const errors = []
    if (!state.email) errors.push({ name: 'email', message: 'campo obbligatorio' })
    if (!state.password) errors.push({ name: 'password', message: 'campo obbligatorio' })
    return errors
}

async function onSubmit(event: FormSubmitEvent<typeof state>) {
    handleLogin()
    //   toast.add({ title: 'Success', description: 'The form has been submitted.', color: 'success' })
    console.log(event.data)
}


// Redirect se già autenticato
onMounted(() => {
    const { isAuthenticated } = useAuth()
    if (isAuthenticated.value) {
        navigateTo('/')
    }
})

const handleLogin = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await $apiFetch('/login', {
            method: 'POST',
            body: {
                email: state.email,
                password: state.password
            }
        })

        token.value = response.data.token
        user.value = response.data.user

        console.log('Login successful:', user.value)

        await navigateTo('/')

    } catch (err) {
        console.error('Login failed:', err)
        error.value = 'Credenziali errate'
    } finally {
        loading.value = false
    }
}
</script>