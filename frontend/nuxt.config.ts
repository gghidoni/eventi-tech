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

  modules: ['@nuxtjs/google-fonts', '@nuxtjs/i18n',],
  googleFonts: {
    families: {
      'JetBrains+Mono': [400, 500, 700],
      'Anta': [400, 500, 700]
    },
    display: 'swap',
  },

  i18n: {
    locales: [
      { code: 'it', name: 'Italiano', file: 'it.json' },
      // { code: 'en', name: 'English', file: 'en.json' }
    ],
    defaultLocale: 'it',
    lazy: true,
    langDir: 'locales/',
    bundle: {
      optimizeTranslationDirective: false
    }
  },
})