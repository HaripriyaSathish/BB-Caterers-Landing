import { useCallback, useEffect, useState } from 'react'
import { ChevronLeft, ChevronRight, X } from 'lucide-react'
import Reveal from '../components/common/Reveal'
import SectionHeading from '../components/common/SectionHeading'
import useParallax from '../hooks/useParallax'

export default function Gallery({ s, items }) {
  const [open, setOpen] = useState(-1)
  const ref = useParallax()
  const count = items.length
  const close = useCallback(() => setOpen(-1), [])
  const step = useCallback((d) => setOpen((i) => (i + d + count) % count), [count])

  useEffect(() => {
    if (open < 0) return
    document.body.classList.add('lightbox-open')
    const onKey = (e) => {
      if (e.key === 'Escape') close()
      if (e.key === 'ArrowRight') step(1)
      if (e.key === 'ArrowLeft') step(-1)
    }
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.classList.remove('lightbox-open')
      window.removeEventListener('keydown', onKey)
    }
  }, [open, close, step])

  if (!count) return null
  const current = items[open]

  return (
    <section id="gallery" className="section gallery" ref={ref}>
      <div className="container">
        <SectionHeading eyebrow={s.gallery_eyebrow} title={s.gallery_title} eyebrowStyle="green" />
        <div className="gallery__grid">
          {items.map((g, i) => (
            <Reveal key={g.id} effect="wipe" delay={(i % 4) * 110} className="gallery__item">
              <button type="button" onClick={() => setOpen(i)} aria-label={`View ${g.title || 'photo'}`}>
                <img src={g.image} alt={g.title || 'Food photo'} loading="lazy" />
                {g.title && (
                  <span className="gallery__caption">
                    <span>{g.title}</span>
                  </span>
                )}
              </button>
            </Reveal>
          ))}
        </div>
      </div>

      {current && (
        <div className="lightbox" role="dialog" aria-modal="true" aria-label={current.title || 'Photo'} onClick={close}>
          <button type="button" className="lightbox__close" onClick={close} aria-label="Close"><X size={26} /></button>
          {count > 1 && (
            <button type="button" className="lightbox__nav lightbox__nav--prev" onClick={(e) => { e.stopPropagation(); step(-1) }} aria-label="Previous photo">
              <ChevronLeft size={28} />
            </button>
          )}
          <figure onClick={(e) => e.stopPropagation()} key={current.id}>
            <img src={current.image} alt={current.title || ''} />
            {current.title && <figcaption>{current.title} <span>{open + 1} / {count}</span></figcaption>}
          </figure>
          {count > 1 && (
            <button type="button" className="lightbox__nav lightbox__nav--next" onClick={(e) => { e.stopPropagation(); step(1) }} aria-label="Next photo">
              <ChevronRight size={28} />
            </button>
          )}
        </div>
      )}
    </section>
  )
}
