<template>
  <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <img class="mx-auto h-10 w-auto"
        src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Sign in to your account</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" action="#" method="POST" @submit.prevent="handleLogin">
        <div>
          <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
          <div class="mt-2">
            <input type="email" v-model="email" name="email" id="email" autocomplete="email" required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
            <div class="text-sm">
              <!-- <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a> -->
            </div>
          </div>
          <div class="mt-2">
            <input type="password" v-model="password" name="password" id="password" autocomplete="current-password"
              required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
          </div>
        </div>

        <div>
          <button type="submit"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign
            in</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const { $apiFetch } = useNuxtApp()
const { token, user } = useAuth()

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
        email: email.value,
        password: password.value
      }
    })

    token.value = response.data.token
    user.value = response.data.user

    console.log('Login successful:', user.value)

    await navigateTo('/')

  } catch (err) {
    console.error('Login failed:', err)
    error.value = 'Credenziali errate o errore di rete'
  } finally {
    loading.value = false
  }
}
</script>