import Reveal from './Reveal'

/** Gold lotus divider (image from Admin → Branding & SEO, with an SVG fallback). */
export default function Divider({ src, className = '' }) {
  return (
    <Reveal effect="zoom-in" className={`lotus-divider ${className}`} aria-hidden="true">
      {src ? (
        <img src={src} alt="" loading="lazy" />
      ) : (
        <svg viewBox="0 0 400 40" fill="none">
          <path d="M0 20h160M240 20h160" stroke="currentColor" strokeWidth="1.5" />
          <path d="M200 6c6 6 6 18 0 26-6-8-6-20 0-26Zm-14 10c8 0 12 8 14 16-8-2-14-8-14-16Zm28 0c-8 0-12 8-14 16 8-2 14-8 14-16Z" fill="currentColor" />
        </svg>
      )}
    </Reveal>
  )
}
