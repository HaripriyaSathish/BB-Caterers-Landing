import axios from 'axios'

// In dev, Vite proxies /api to the PHP backend (see vite.config.js).
// In production the backend lives on the same domain. Override with VITE_API_URL if needed.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  timeout: 15000,
  headers: { Accept: 'application/json' },
})

/** All landing-page content in one request. */
export async function fetchSite() {
  const { data } = await api.get('/site.php')
  if (!data?.success) throw new Error(data?.message || 'Could not load content')
  return data.data
}

/**
 * Submit the enquiry form.
 * Resolves with { message } or rejects with { message, errors } (field → message).
 */
export async function sendEnquiry(payload) {
  try {
    const { data } = await api.post('/enquiry.php', payload, {
      headers: { 'Content-Type': 'application/json' },
    })
    return data
  } catch (err) {
    const res = err.response?.data
    throw {
      message: res?.message || 'Could not send your enquiry. Please check your connection and try again.',
      errors: res?.errors || {},
    }
  }
}

export default api
