// composables/useAuth.js
export const useAuth = () => {
  const config = useRuntimeConfig()
  const tokenExpirySeconds = parseInt(config.public.tokenExpirySeconds)

  const token = useCookie('token', {
    maxAge: tokenExpirySeconds,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'Strict',
  })

  const user = useCookie('user', {
    maxAge: tokenExpirySeconds,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'Strict',
  })

  // Computed per verificare se l'utente è autenticato
  const isAuthenticated = computed(() => {
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