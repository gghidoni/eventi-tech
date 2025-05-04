import { defineNuxtPlugin } from '#app'
 
export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const token = useCookie('token')

  const apiFetch = $fetch.create({
    baseURL: config.public.apiBaseUrl,

    onRequest({ options }) {
      if (token.value) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token.value}`
        }
      }
    },

    onResponseError({ response }) {
      if (response.status === 401) {
        token.value = null
        return navigateTo('/login')
      }
    }
  })

  nuxtApp.provide('apiFetch', apiFetch)
})