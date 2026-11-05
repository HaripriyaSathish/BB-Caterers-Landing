import { useEffect } from 'react'
import Lenis from 'lenis'

/**
 * Inertia ("buttery") page scrolling with Lenis.
 * Skipped for visitors who prefer reduced motion and on touch devices (native scrolling is better there).
 */
export default function useSmoothScroll() {
  useEffect(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const touch = window.matchMedia('(hover: none)').matches
    if (reduced || touch) return

    // Light smoothing: lerp 0.3 = very light smoothing (Lenis default is 0.1 — higher = less floaty)
    const lenis = new Lenis({ lerp: 0.3, wheelMultiplier: 1, smoothWheel: true })
    window.__lenis = lenis
    let raf = 0
    const loop = (time) => {
      lenis.raf(time)
      raf = requestAnimationFrame(loop)
    }
    raf = requestAnimationFrame(loop)

    // Pause while the mobile menu / lightbox lock the page
    const obs = new MutationObserver(() => {
      const locked = document.body.classList.contains('menu-open') || document.body.classList.contains('lightbox-open')
      locked ? lenis.stop() : lenis.start()
    })
    obs.observe(document.body, { attributes: true, attributeFilter: ['class'] })

    return () => {
      cancelAnimationFrame(raf)
      obs.disconnect()
      lenis.destroy()
      delete window.__lenis
    }
  }, [])
}
