import Reveal from '../components/common/Reveal'
import Divider from '../components/common/Divider'
import SectionHeading from '../components/common/SectionHeading'
import Icon from '../components/common/Icon'

export default function Features({ s, items }) {
  if (!items.length) return null
  return (
    <section id="why-us" className="section features">
      <div className="container">
        <Divider src={s.theme_divider} />
        <SectionHeading eyebrow={s.features_eyebrow} title={s.features_title} italic eyebrowStyle="diamonds" />
        <div className="features__grid">
          {items.map((f, i) => (
            <Reveal as="article" key={f.id} effect="fade-up" delay={i * 90} className="feature-card">
              <span className="icon-badge icon-badge--ring"><Icon name={f.icon} size={30} strokeWidth={1.6} /></span>
              <h3>{f.title}</h3>
              {f.description && <p>{f.description}</p>}
            </Reveal>
          ))}
        </div>
        <Divider src={s.theme_divider} className="lotus-divider--bottom" />
      </div>
    </section>
  )
}
