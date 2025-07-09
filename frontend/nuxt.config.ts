import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL,
      tokenExpirySeconds: 3600
    }
  },
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  css: ['~/assets/css/main.css'],

  devServer: {
    port: 5173
  },

  modules: ['@nuxtjs/google-fonts'],
  googleFonts: {
    families: {
      'JetBrains+Mono': [400, 500, 700],
      'Anta': [400, 500, 700]
    },
    display: 'swap',
  },
})