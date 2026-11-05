import Reveal from '../components/common/Reveal'
import Divider from '../components/common/Divider'
import Icon from '../components/common/Icon'
import { animatedWords } from '../utils/format'
import useParallax from '../hooks/useParallax'
import tiltHandlers from '../hooks/useTilt'

export default function Events({ s, items }) {
  const ref = useParallax()
  if (!items.length) return null
  return (
    <section id="events" className="section events" ref={ref}>
      <Reveal effect="arch" duration={1300} className={`events__arch ${s.theme_arch ? "events__arch--image" : ""}`} aria-hidden="true">
        {s.theme_arch ? (
          <>
            <img className="events__arch-img" src={s.theme_arch} alt="" decoding="async" />
            <span className="events__arch-fill" />
          </>
        ) : (
          <>
            <span className="events__arch-shape events__arch-shape--gold" />
            <span className="events__arch-shape" />
          </>
        )}
      </Reveal>
      {s.theme_flourish && (
        <>
          <img className="events__leaf events__leaf--left" src={s.theme_flourish} alt="" aria-hidden="true" loading="lazy" />
          <img className="events__leaf events__leaf--right" src={s.theme_flourish} alt="" aria-hidden="true" loading="lazy" />
        </>
      )}
      <div className="container events__inner">
        <Reveal as="h2" effect="words" className="events__title" aria-label={(s.events_title || '').replace(/\*/g, '')}>{animatedWords(s.events_title, 'text-gold')}</Reveal>
        <Divider src={s.theme_divider} />
        <div className="events__grid">
          {items.map((ev, i) => (
            <Reveal as="article" key={ev.id} effect="flip-up" delay={i * 120} className="event-cell">
              <div className="event-card tilt" {...tiltHandlers()}>
                <div className="event-card__media">
                  {ev.image && <img src={ev.image} alt={ev.title} loading="lazy" />}
                </div>
                <span className="icon-badge event-card__icon"><Icon name={ev.icon} size={20} /></span>
                <h3>{ev.title}</h3>
              </div>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  )
}
