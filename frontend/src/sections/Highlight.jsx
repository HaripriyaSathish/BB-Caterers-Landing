import Reveal from '../components/common/Reveal'
import Icon from '../components/common/Icon'
import { rich } from '../utils/format'

export default function Highlight({ s, items }) {
  return (
    <section className="section section--tight highlight" aria-label={s.highlight_title}>
      <div className="container">
        <Reveal effect="zoom-in" className="highlight__card" style={s.highlight_image ? { '--banner': `url("${s.highlight_image}")` } : undefined}>
          {s.highlight_image && s.highlight_image_alt && <span className="sr-only" role="img" aria-label={s.highlight_image_alt} />}
          <div className="highlight__content">
            {s.highlight_title && <Reveal as="h2" effect="blur-in" delay={150}>{s.highlight_title}</Reveal>}
            <Reveal effect="grow" delay={300} className="highlight__rule" aria-hidden="true">
              <svg width="22" height="12" viewBox="0 0 22 12" fill="currentColor"><path d="M11 0c1.6 3.4 5 6 11 7-4 .6-7.4 2.4-11 5C7.4 9.4 4 7.6 0 7c6-1 9.4-3.6 11-7Z" /></svg>
            </Reveal>
            {s.highlight_text && <Reveal as="p" effect="fade-up" delay={250} className="highlight__text">{rich(s.highlight_text)}</Reveal>}
            {items.length > 0 && (
              <ul className="highlight__list">
                {items.map((h, i) => (
                  <Reveal as="li" key={h.id} effect="fade-left" delay={250 + i * 110}>
                    <span className="icon-badge icon-badge--sm"><Icon name={h.icon} size={17} /></span>
                    {h.title}
                  </Reveal>
                ))}
              </ul>
            )}
          </div>
        </Reveal>
      </div>
    </section>
  )
}
