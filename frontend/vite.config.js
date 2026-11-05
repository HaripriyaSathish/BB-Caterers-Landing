import react from '@vitejs/plugin-react'
import { defineConfig, loadEnv } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  // PHP backend (bb_caterers/backend) — start it with:  php -S 127.0.0.1:8090 router.php
  const backend = env.VITE_BACKEND_URL || 'http://127.0.0.1:8090'

  return {
    plugins: [react()],
    server: {
      port: 5173,
      // /admin, /api and /media are served by PHP, so the admin panel opens at
      // http://localhost:5173/admin and the site can call /api without CORS.
      proxy: {
        '/admin': { target: backend, changeOrigin: false },
        '/api': { target: backend, changeOrigin: false },
        '/media': { target: backend, changeOrigin: false },
      },
    },
  }
})
