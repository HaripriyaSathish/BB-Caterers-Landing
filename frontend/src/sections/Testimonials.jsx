import { useCallback, useEffect, useRef, useState } from 'react'
import { ChevronLeft, ChevronRight, Star } from 'lucide-react'
import SectionHeading from '../components/common/SectionHeading'
import Reveal from '../components/common/Reveal'

/** How many cards fit side by side at the current width. */
function usePerView() {
  const get = () => (window.innerWidth >= 1024 ? 3 : window.innerWidth >= 680 ? 2 : 1)
  const [n, setN] = useState(get)
  useEffect(() => {
    const onResize = () => setN(get())
    window.addEventListener('resize', onResize)
    return () => window.removeEventListener('resize', onResize)
  }, [])
  return n
}

/**
 * Looping carousel as in the Figma: arrows on both sides and one dot per review.
 * Each step moves one card; it always loops, even with only 3 reviews.
 */
export default function Testimonials({ s, items }) {
  const perView = usePerView()
  const count = items.length
  const [start, setStart] = useState(0)
  const [dir, setDir] = useState(1)
  const [paused, setPaused] = useState(false)
  const touch = useRef(null)

  const go = useCallback(
    (i, d) => {
      if (!count) return
      setDir(d ?? 1)
      setStart(((i % count) + count) % count)
    },
    [count],
  )
  const next = () => go(start + 1, 1)
  const prev = () => go(start - 1, -1)

  useEffect(() => {
    if (paused || count < 2) return
    const t = setTimeout(() => go(start + 1, 1), 5000)
    return () => clearTimeout(t)
  }, [start, paused, count, go])

  if (!count) return null

  // Cards shown: `perView` reviews starting at `start`, wrapping round the list
  const shown = Array.from({ length: Math.min(perView, count) }, (_, k) => items[(start + k) % count])

  return (
    <section id="testimonials" className="section testimonials">
      <div className="container">
        <SectionHeading eyebrow={s.testimonials_eyebrow} title={s.testimonials_title} italic eyebrowStyle="green" />
        <Reveal effect="fade-up" delay={150}>
          <div
            className="testimonials__slider"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onTouchStart={(e) => (touch.current = e.touches[0].clientX)}
            onTouchEnd={(e) => {
              if (touch.current == null) return
              const dx = e.changedTouches[0].clientX - touch.current
              if (Math.abs(dx) > 40) (dx < 0 ? next : prev)()
              touch.current = null
            }}
          >
            <button type="button" className="round-btn testimonials__nav testimonials__nav--prev" onClick={prev} aria-label="Previous review">
              <ChevronLeft size={20} />
            </button>
            <div className="testimonials__viewport" aria-live="polite">
              <div key={start} className={`testimonials__track ${dir > 0 ? 'from-right' : 'from-left'}`} style={{ '--per': perView }}>
                {shown.map((t, k) => (
                  <figure className="testimonial-card" key={`${t.id}-${k}`} style={{ '--k': k }}>
                    <div className="testimonial-card__inner">
                      <div className="stars" role="img" aria-label={`${t.rating} out of 5 stars`}>
                        {Array.from({ length: 5 }, (_, i) => (
                          <Star key={i} size={13} fill={i < t.rating ? 'currentColor' : 'none'} className={i < t.rating ? '' : 'is-off'} />
                        ))}
                      </div>
                      <blockquote>“{t.message}”</blockquote>
                      <figcaption>
                        {t.photo && <img src={t.photo} alt="" loading="lazy" />}
                        <span>
                          <strong>{t.name}</strong>
                          {t.role && <small>{t.role}</small>}
                        </span>
                      </figcaption>
                    </div>
                  </figure>
                ))}
              </div>
            </div>
            <button type="button" className="round-btn testimonials__nav testimonials__nav--next" onClick={next} aria-label="Next review">
              <ChevronRight size={20} />
            </button>
          </div>
          <div className="dots" role="tablist" aria-label="Choose review">
            {items.map((t, i) => (
              <button
                key={t.id}
                type="button"
                role="tab"
                aria-selected={i === start}
                aria-label={`Review ${i + 1}`}
                className={i === start ? 'is-active' : ''}
                onClick={() => go(i, i >= start ? 1 : -1)}
              />
            ))}
          </div>
        </Reveal>
      </div>
    </section>
  )
}
