import Reveal from '../components/common/Reveal'
import SectionHeading from '../components/common/SectionHeading'
import Icon from '../components/common/Icon'
import { pad2 } from '../utils/format'
import tiltHandlers from '../hooks/useTilt'

export default function Services({ s, items }) {
  if (!items.length) return null
  return (
    <section id="services" className="section services">
      <div className="container">
        <SectionHeading eyebrow={s.services_eyebrow} title={s.services_title} eyebrowStyle="dashes" />
        <div className="services__grid">
          {items.map((item, i) => (
            <Reveal as="article" key={item.id} effect="rise" delay={(i % 3) * 120} className="service-cell">
              <div className="service-card tilt" {...tiltHandlers()}>
                <div className="service-card__media">
                  {item.image && <img src={item.image} alt={item.title} loading="lazy" />}
                </div>
                <span className="icon-badge service-card__icon"><Icon name={item.icon} size={22} /></span>
                <div className="service-card__body">
                  <h3>{pad2(i + 1)}. {item.title}</h3>
                  {item.description && <p>{item.description}</p>}
                </div>
              </div>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  )
}
