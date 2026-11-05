import { useId, useState } from 'react'
import { Plus } from 'lucide-react'
import Reveal from '../components/common/Reveal'
import SectionHeading from '../components/common/SectionHeading'

/** FAQ accordion (Admin → FAQ). Answers stay in the page so search engines can read them. */
export default function Faq({ s, items }) {
  const [open, setOpen] = useState(0)
  const uid = useId()
  if (!items.length) return null

  // FAQPage structured data for Google
  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: items.map((f) => ({
      '@type': 'Question',
      name: f.question,
      acceptedAnswer: { '@type': 'Answer', text: f.answer },
    })),
  }

  return (
    <section id="faq" className="section faq">
      <script type="application/ld+json">{JSON.stringify(jsonLd)}</script>
      <div className="container faq__grid">
        <div className="faq__intro">
          <SectionHeading eyebrow={s.faq_eyebrow} title={s.faq_title} align="left" italic eyebrowStyle="green" />
          {s.faq_text && <Reveal as="p" effect="fade-up" delay={150}>{s.faq_text}</Reveal>}
        </div>
        <div className="faq__list">
          {items.map((f, i) => {
            const isOpen = open === i
            const btnId = `${uid}-q${f.id}`
            const panelId = `${uid}-a${f.id}`
            return (
              <Reveal key={f.id} effect="fade-up" delay={i * 80} className={`faq-item ${isOpen ? 'is-open' : ''}`}>
                <h3>
                  <button
                    type="button"
                    id={btnId}
                    aria-expanded={isOpen}
                    aria-controls={panelId}
                    onClick={() => setOpen(isOpen ? -1 : i)}
                  >
                    <span>{f.question}</span>
                    <Plus size={20} className="faq-item__icon" aria-hidden="true" />
                  </button>
                </h3>
                <div className="faq-item__panel" id={panelId} role="region" aria-labelledby={btnId}>
                  <div className="faq-item__inner">
                    <p>{f.answer}</p>
                  </div>
                </div>
              </Reveal>
            )
          })}
        </div>
      </div>
    </section>
  )
}
