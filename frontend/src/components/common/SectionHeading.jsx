import Reveal from './Reveal'
import { animatedWords } from '../../utils/format'

/**
 * Eyebrow + serif title (+ optional gold rule), as used across the Figma design.
 * eyebrowStyle: 'gold' | 'green' | 'dashes' (gold with — —) | 'diamonds' (◆ label ◆)
 */
export default function SectionHeading({ eyebrow, title, align = 'center', tone = 'dark', italic = false, eyebrowStyle = 'gold', as: H = 'h2', className = '' }) {
  const eyebrowClass = {
    gold: '',
    green: 'eyebrow--green',
    dashes: 'eyebrow--dashes',
    diamonds: 'eyebrow--diamonds',
  }[eyebrowStyle] ?? ''
  return (
    <div className={`section-heading section-heading--${align} section-heading--${tone} ${className}`}>
      {eyebrow && (
        <Reveal as="p" effect="fade-up" className={`eyebrow ${eyebrowClass}`}>
          {eyebrow}
        </Reveal>
      )}
      {title && (
        <Reveal as={H} effect="words" delay={120} className={`section-title ${italic ? 'is-italic' : ''}`} aria-label={title.replace(/\*/g, '')}>
          {animatedWords(title)}
        </Reveal>
      )}
      <Reveal effect="grow" delay={260} className="section-rule" aria-hidden="true" />
    </div>
  )
}
