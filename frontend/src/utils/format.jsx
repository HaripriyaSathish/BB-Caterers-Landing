/** "Made for *Moments* Worth *Celebrating*" → words between asterisks rendered in gold. */
export function highlight(text, className = 'text-gold') {
  if (!text) return null
  return text.split(/(\*[^*]+\*)/g).map((part, i) =>
    part.startsWith('*') && part.endsWith('*') ? (
      <span key={i} className={className}>
        {part.slice(1, -1)}
      </span>
    ) : (
      part
    ),
  )
}

/**
 * Split a title into word spans for the word-by-word reveal; *gold* phrases stay gold
 * (a phrase may span several words: "*Worth Celebrating*").
 */
export function animatedWords(text, goldClass = 'text-gold') {
  if (!text) return null
  let gold = false
  return text.split(/\s+/).map((tok, i, arr) => {
    const starts = tok.startsWith('*')
    const ends = tok.endsWith('*') && (tok.length > 1 || !starts)
    if (starts) gold = true
    const word = tok.replace(/\*/g, '')
    const cls = 'w' + (gold ? ' ' + goldClass : '')
    if (ends) gold = false
    return (
      <span key={i} className={cls} style={{ '--wd': `${i * 60}ms` }}>
        {word}{i < arr.length - 1 ? ' ' : ''}
      </span>
    )
  })
}

/** Remove the *markers* for plain-text use (alt text, aria labels). */
export const plain = (text = '') => text.replace(/\*/g, '')

export const pad2 = (n) => String(n).padStart(2, '0')

/** Smooth-scroll for in-page links like "#contact"; returns true when handled. */
export function scrollToHash(href) {
  if (!href?.startsWith('#')) return false
  const el = href === '#home' ? document.body : document.querySelector(href)
  if (!el) return false
  const top = href === '#home' ? 0 : el.getBoundingClientRect().top + window.scrollY - 70
  if (window.__lenis) window.__lenis.scrollTo(top, { duration: 0.7 })
  else window.scrollTo({ top, behavior: 'smooth' })
  history.replaceState(null, '', href)
  return true
}

export const telHref = (phone = '') => 'tel:' + phone.replace(/[^\d+]/g, '')
