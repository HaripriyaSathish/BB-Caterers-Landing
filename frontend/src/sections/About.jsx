import { useEffect, useRef, useState } from 'react'
import Reveal from '../components/common/Reveal'
import SectionHeading from '../components/common/SectionHeading'
import { rich } from '../utils/format'

export default function About({ s }) {
  const ref = useRef(null)
  const [shift, setShift] = useState(0)

  // Scroll parallax: the two photos drift in opposite directions
  useEffect(() => {
    let raf = 0
    const onScroll = () => {
      cancelAnimationFrame(raf)
      raf = requestAnimationFrame(() => {
        const el = ref.current
        if (!el) return
        const r = el.getBoundingClientRect()
        const p = (r.top + r.height / 2 - window.innerHeight / 2) / window.innerHeight // -1 … 1
        setShift(Math.max(-1, Math.min(1, p)))
      })
    }
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => {
      cancelAnimationFrame(raf)
      window.removeEventListener('scroll', onScroll)
    }
  }, [])

  return (
    <section id="about" className="section about">
      <div className="container about__grid">
        <div className="about__media" ref={ref}>
          {s.about_image_1 && (
            <Reveal effect="fade-right" className="about__photo about__photo--main" style={{ '--py': `${shift * -24}px` }}>
              <img src={s.about_image_1} alt="Traditional South Indian dosa with chutneys" loading="lazy" />
            </Reveal>
          )}
          {s.about_image_2 && (
            <Reveal effect="zoom-in" delay={250} className="about__photo about__photo--small" style={{ '--py': `${shift * 30}px` }}>
              <img src={s.about_image_2} alt="Golden vadas with chutney" loading="lazy" />
            </Reveal>
          )}
        </div>

        <div className="about__text">
          <SectionHeading eyebrow={s.about_eyebrow} title={s.about_title} align="left" />
          {s.about_text && <Reveal as="p" effect="fade-up" delay={150} className="lead">{rich(s.about_text)}</Reveal>}
          {s.about_text_2 && <Reveal as="p" effect="fade-up" delay={250}>{rich(s.about_text_2)}</Reveal>}
        </div>
      </div>
    </section>
  )
}
