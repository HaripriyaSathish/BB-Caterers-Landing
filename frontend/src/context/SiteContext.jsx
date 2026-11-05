import { createContext, useCallback, useContext, useEffect, useState } from 'react'
import { fetchSite } from '../services/api'

const SiteContext = createContext(null)

export function SiteProvider({ children }) {
  const [state, setState] = useState({ data: null, loading: true, error: null })

  const request = useCallback(
    () =>
      fetchSite()
        .then((data) => setState({ data, loading: false, error: null }))
        .catch((error) => setState({ data: null, loading: false, error })),
    [],
  )

  // Initial load
  useEffect(() => {
    request()
  }, [request])

  // "Try again" button
  const reload = useCallback(() => {
    setState((s) => ({ ...s, loading: true, error: null }))
    request()
  }, [request])

  return <SiteContext.Provider value={{ ...state, reload }}>{children}</SiteContext.Provider>
}

// eslint-disable-next-line react-refresh/only-export-components
export function useSite() {
  const ctx = useContext(SiteContext)
  if (!ctx) throw new Error('useSite must be used inside <SiteProvider>')
  return ctx
}
