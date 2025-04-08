import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    tailwindcss()
  ],
  server: {
    host: true, // Ascolta su tutte le interfacce
    port: 5173   // Assicurati che il server stia usando la porta corretta
  }
})
