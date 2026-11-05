import { useEffect, useState } from 'react'

/** Current window scroll position (rAF-throttled). */
export function useScrollY() {
  const [y, setY] = useState(() => (typeof window === 'undefined' ? 0 : window.scrollY))
  useEffect(() => {
    let raf = 0
    const onScroll = () => {
      cancelAnimationFrame(raf)
      raf = requestAnimationFrame(() => setY(window.scrollY))
    }
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => {
      cancelAnimationFrame(raf)
      window.removeEventListener('scroll', onScroll)
    }
  }, [])
  return y
}

/** Id of the section currently in the middle of the viewport (for the active nav link). */
export function useActiveSection(ids) {
  const [active, setActive] = useState(ids[0] || '')
  const key = ids.join(',')
  useEffect(() => {
    const els = key.split(',').map((id) => document.getElementById(id)).filter(Boolean)
    if (!els.length) return
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => e.isIntersecting && setActive(e.target.id))
      },
      { rootMargin: '-45% 0px -50% 0px' },
    )
    els.forEach((el) => io.observe(el))
    return () => io.disconnect()
  }, [key])
  return active
}
