import { useCallback, useEffect, useRef, useState } from 'react'
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-react'
import SmartLink from '../components/common/SmartLink'
import { pad2, rich } from '../utils/format'
import { useScrollY } from '../hooks/useScroll'

export default function Hero({ slides, autoplaySeconds = 6 }) {
  const [index, setIndex] = useState(0)
  const [paused, setPaused] = useState(false)
  const touch = useRef(null)
  const count = slides.length
  const ms = Math.max(0, Number(autoplaySeconds) || 0) * 1000
  const y = useScrollY()

  const go = useCallback((i) => setIndex(((i % count) + count) % count), [count])
  const next = useCallback(() => go(index + 1), [go, index])
  const prev = useCallback(() => go(index - 1), [go, index])

  useEffect(() => {
    if (!ms || paused || count < 2) return
    const t = setTimeout(next, ms)
    return () => clearTimeout(t)
  }, [index, ms, paused, count, next])

  // Pause when the tab is hidden
  useEffect(() => {
    const onVis = () => setPaused(document.hidden)
    document.addEventListener('visibilitychange', onVis)
    return () => document.removeEventListener('visibilitychange', onVis)
  }, [])

  if (!count) return <section id="home" className="hero hero--empty" />

  const onKey = (e) => {
    if (e.key === 'ArrowRight') next()
    if (e.key === 'ArrowLeft') prev()
  }
  const onTouchStart = (e) => (touch.current = e.touches[0].clientX)
  const onTouchEnd = (e) => {
    if (touch.current == null) return
    const dx = e.changedTouches[0].clientX - touch.current
    if (Math.abs(dx) > 45) (dx < 0 ? next : prev)()
    touch.current = null
  }

  // Gentle parallax while scrolling past the hero
  const parallax = Math.min(y, 600) * 0.2

  return (
    <section
      id="home"
      className="hero"
      aria-roledescription="carousel"
      aria-label="Highlights"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onKeyDown={onKey}
      onTouchStart={onTouchStart}
      onTouchEnd={onTouchEnd}
    >
      {slides.map((s, i) => (
        <div
          key={s.id}
          className={`hero__slide ${i === index ? 'is-active' : ''}`}
          role="group"
          aria-roledescription="slide"
          aria-label={`${i + 1} of ${count}`}
          aria-hidden={i !== index}
        >
          <div className="hero__media" style={{ transform: `translate3d(0, ${parallax}px, 0)` }}>
            {s.image && (
              <img src={s.image} alt="" fetchPriority={i === 0 ? 'high' : 'low'} loading={i === 0 ? 'eager' : 'lazy'} decoding="async" />
            )}
          </div>
          <div className="hero__shade" aria-hidden="true" />
          <div className="container container--wide hero__content">
            {s.eyebrow && (
              <p className="hero__eyebrow">
                <span>{pad2(i + 1)}</span> — {s.eyebrow}
              </p>
            )}
            {/* Words animate in one by one but wrap naturally, exactly like the Figma text box */}
            {(() => {
              const Tag = i === 0 ? 'h1' : 'h2'
              return (
                <Tag className="hero__title" aria-label={s.title}>
                  {s.title.split(/\s+/).map((word, wi, arr) => (
                    <span key={wi} aria-hidden="true" className="hero__word" style={{ '--d': `${200 + wi * 70}ms` }}>
                      {word}{wi < arr.length - 1 ? ' ' : ''}
                    </span>
                  ))}
                </Tag>
              )
            })()}
            {s.subtitle && <p className="hero__subtitle">{rich(s.subtitle)}</p>}
            <div className="hero__actions">
              {s.btn1_text && (
                <SmartLink href={s.btn1_link || '#services'} className="btn btn--gold btn--lg" tabIndex={i === index ? 0 : -1}>
                  {s.btn1_text} <ArrowRight size={18} />
                </SmartLink>
              )}
              {s.btn2_text && (
                <SmartLink href={s.btn2_link || '#contact'} className="btn btn--outline-light btn--lg" tabIndex={i === index ? 0 : -1}>
                  {s.btn2_text} <ArrowRight size={18} />
                </SmartLink>
              )}
            </div>
          </div>
        </div>
      ))}

      {count > 1 && (
        <div className="container container--wide hero__controls">
          <div className="hero__dots" role="tablist" aria-label="Choose slide">
            {slides.map((s, i) => (
              <button
                key={s.id}
                type="button"
                role="tab"
                aria-selected={i === index}
                aria-label={`Slide ${i + 1}`}
                className={`hero__dot ${i === index ? 'is-active' : ''}`}
                onClick={() => go(i)}
              >
                <span style={i === index && ms && !paused ? { animationDuration: `${ms}ms` } : undefined} className={i === index && ms ? 'is-running' : ''} />
              </button>
            ))}
          </div>
          <div className="hero__arrows">
            <button type="button" className="round-btn round-btn--light" onClick={prev} aria-label="Previous slide"><ChevronLeft size={22} /></button>
            <button type="button" className="round-btn round-btn--light" onClick={next} aria-label="Next slide"><ChevronRight size={22} /></button>
          </div>
        </div>
      )}
    </section>
  )
}
