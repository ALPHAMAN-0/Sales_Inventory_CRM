import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,          // bind 0.0.0.0 so the port is reachable from the host
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost', // the browser connects the HMR socket back to localhost
      clientPort: 5173,
    },
    watch: {
      usePolling: true,  // bind mounts over VirtioFS don't emit inotify events reliably
      interval: 100,
    },
    // Fallback for Sanctum "cookie hell": uncomment to make the API same-origin.
    // proxy: {
    //   '/api':     { target: 'http://web:80', changeOrigin: true },
    //   '/sanctum': { target: 'http://web:80', changeOrigin: true },
    //   '/login':   { target: 'http://web:80', changeOrigin: true },
    //   '/logout':  { target: 'http://web:80', changeOrigin: true },
    // },
  },
})
