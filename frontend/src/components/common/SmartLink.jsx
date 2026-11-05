import { scrollToHash } from '../../utils/format'

/** <a> that smooth-scrolls for "#section" links and opens external links in a new tab. */
export default function SmartLink({ href = '#', onClick, children, ...rest }) {
  const external = /^https?:\/\//i.test(href)
  const handle = (e) => {
    if (href.startsWith('#') && scrollToHash(href)) e.preventDefault()
    onClick?.(e)
  }
  return (
    <a href={href} onClick={handle} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : null)} {...rest}>
      {children}
    </a>
  )
}
