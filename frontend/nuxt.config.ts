export default defineNuxtConfig({
  compatibilityDate: '2026-05-11',
  devtools: { enabled: true },

  css: ['~/assets/css/main.css'],

  app: {
    head: {
      titleTemplate: '%s | Surat App',
      title: 'Surat App',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#0f172a' },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
      appName: process.env.NUXT_PUBLIC_APP_NAME || 'Surat App',
    },
  },
})
