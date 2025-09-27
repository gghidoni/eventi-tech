// composables/useAuth.ts
import { computed } from 'vue'
import { useRuntimeConfig, useCookie, navigateTo } from '#app'

export const useAuth = () => {
  const config = useRuntimeConfig()
  const tokenExpirySeconds: number = parseInt(config.public.tokenExpirySeconds as string, 10)

  const token = useCookie<string | null>('token', {
    maxAge: tokenExpirySeconds,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'Strict',
  })

  const user = useCookie<string | null>('user', {
    maxAge: tokenExpirySeconds,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'Strict',
  })

  // Computed per verificare se l'utente è autenticato
  const isAuthenticated = computed<boolean>(() => {
    return !!(token.value && user.value)
  })

  // Funzione per il logout
  const logout = () => {
    token.value = null
    user.value = null
    navigateTo('/login')
  }

  return {
    token,
    user,
    isAuthenticated,
    logout
  }
}