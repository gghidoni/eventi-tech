<template>
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold">Registrati</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

            <UAlert v-if="error" color="error" variant="soft" :title="error"
                :close-button="{ icon: 'i-heroicons-x-mark-20-solid', color: 'gray', variant: 'link' }"
                @close="error = ''" class="mb-4" />

            <!-- Aggiungi ref al form per poterlo controllare -->
            <UForm ref="form" :validate="validate" :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="nome" name="name">
                    <UInput v-model="state.name" class="w-full" :ui="customInputUI" />
                </UFormField>
                <UFormField label="email" name="email">
                    <UInput v-model="state.email" class="w-full" :ui="customInputUI" />
                </UFormField>

                <UFormField label="password" name="password">
                    <UInput v-model="state.password" :type="showPassword ? 'text' : 'password'" class="w-full"
                        :ui="customPasswordInputUI">
                        <template #trailing>
                            <UButton color="neutral" variant="link" size="sm"
                                :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                :aria-pressed="showPassword" aria-controls="password"
                                @click="showPassword = !showPassword" />
                        </template>
                    </UInput>
                </UFormField>

                <UFormField label="conferma password" name="confirmPassword">
                    <UInput v-model="state.confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full" :ui="customPasswordInputUI">
                        <template #trailing>
                            <UButton color="neutral" variant="link" size="sm"
                                :icon="showConfirmPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                                :aria-pressed="showConfirmPassword" aria-controls="password"
                                @click="showConfirmPassword = !showConfirmPassword" />
                        </template>
                    </UInput>
                </UFormField>

                <UButton type="submit" class="text-background mt-4" :loading="loading">
                    registrati
                    <img class="ml-3 w-3" src="/icons/right-black.svg" alt="">
                </UButton>
            </UForm>

        </div>
    </div>
</template>

<script setup lang="ts">
import type { FormError, FormSubmitEvent, FormErrorEvent } from '@nuxt/ui'

const loading = ref(false)
const error = ref('')
const { customInputUI, customPasswordInputUI } = useCustomUI()

const { $apiFetch } = useNuxtApp()
const { token, user } = useAuth()

// Ref per il form
const form = ref()

const state = reactive({
    name: undefined,
    email: undefined,
    password: undefined,
    confirmPassword: undefined
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const backendErrors = reactive<FormError[]>([])

watch(state, () => {
  backendErrors.splice(0)
}, { deep: true })


const validate = (state: any): FormError[] => {
    // const errors: FormError[] = []
    // backendErrors.splice(0, backendErrors.length)
    const errors = []
    if (!state.name) errors.push({ name: 'name', message: 'campo obbligatorio' })
    if (!state.email) errors.push({ name: 'email', message: 'campo obbligatorio' })
    if (!state.password) errors.push({ name: 'password', message: 'campo obbligatorio' })
    if (!state.confirmPassword) errors.push({ name: 'confirmPassword', message: 'campo obbligatorio' })
    if (state.password !== state.confirmPassword) errors.push({ name: 'confirmPassword', message: 'le password non coincidono' })

    errors.push(...backendErrors)

    return errors
}

async function onSubmit(event: FormSubmitEvent<typeof state>) {
    console.log('invio!')
    await handleRegister()
    console.log(event.data)
}

async function onError(event: FormErrorEvent) {
    if (event?.errors?.[0]?.id) {
        const element = document.getElementById(event.errors[0].id)
        element?.focus()
        element?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
}

const handleRegister = async () => {
    backendErrors.splice(0, backendErrors.length)
    loading.value = true
    error.value = ''

    try {
        const response = await $apiFetch('/register', {
            method: 'POST',
            body: {
                name: state.name,
                email: state.email,
                password: state.password
            }
        })

        token.value = response.data.token
        user.value = response.data.user

        console.log('Register successful:', user.value)
        await navigateTo('/')

    } catch (err: any) {
        console.log('Registration failed:', err.data)

        if (err.data?.errors) {
            // Aggiungi gli errori dal backend
            Object.entries(err.data.errors).forEach(([field, messages]) => {
                (messages as string[]).forEach((message) => {
                    backendErrors.push({ name: field, message })
                })
            })

            // IMPORTANTE: Forza la validazione per mostrare gli errori
            await nextTick()
            if (form.value) {
                form.value.validate()
            }
        } else {
            error.value = err.data?.message || 'Errore durante la registrazione'
        }

        console.log('Backend errors:', backendErrors)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    const { isAuthenticated } = useAuth()
    if (isAuthenticated.value) {
        navigateTo('/')
    }
})
</script>