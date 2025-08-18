import { defineNuxtPlugin } from '#app'
import type { $Fetch } from 'ofetch'

declare module '#app' {
  interface NuxtApp {
    $apiFetch: $Fetch
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $apiFetch: $Fetch
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('token')

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