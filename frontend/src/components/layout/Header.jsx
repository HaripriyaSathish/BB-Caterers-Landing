import { useEffect, useState } from 'react'
import { ArrowRight, Menu, X } from 'lucide-react'
import SmartLink from '../common/SmartLink'
import { useActiveSection, useScrollY } from '../../hooks/useScroll'

export function Brand({ settings, light = false }) {
  const [first, ...rest] = (settings.site_name || 'BB Caterers').split(' ')
  return (
    <span className={`brand ${light ? 'brand--light' : ''}`}>
      {settings.logo && <img className="brand__logo" src={settings.logo} alt={settings.site_name} width="148" height="105" />}
      <span className="brand__text">
        <span className="brand__name">
          <span className="brand__first">{first}</span> {rest.join(' ')}
        </span>
        {settings.site_tagline && <span className="brand__tagline">{settings.site_tagline}</span>}
      </span>
    </span>
  )
}

export default function Header({ settings, menu }) {
  const y = useScrollY()
  const [open, setOpen] = useState(false)
  const ids = menu.filter((m) => m.url.startsWith('#')).map((m) => m.url.slice(1))
  const active = useActiveSection(ids)
  const scrolled = y > 40

  // Reading-progress bar (re-computed on every scroll render)
  const scrollable = document.documentElement.scrollHeight - window.innerHeight
  const progress = scrollable > 0 ? Math.min(1, y / scrollable) : 0

  // Lock page scroll while the mobile menu is open; close on Escape / resize to desktop
  useEffect(() => {
    document.body.classList.toggle('menu-open', open)
    const onKey = (e) => e.key === 'Escape' && setOpen(false)
    const onResize = () => window.innerWidth > 991 && setOpen(false)
    window.addEventListener('keydown', onKey)
    window.addEventListener('resize', onResize)
    return () => {
      window.removeEventListener('keydown', onKey)
      window.removeEventListener('resize', onResize)
    }
  }, [open])

  const close = () => setOpen(false)

  return (
    <header className={`site-header ${scrolled ? 'is-scrolled' : ''} ${open ? 'is-open' : ''}`}>
      <div className="scroll-progress" style={{ transform: `scaleX(${progress})` }} aria-hidden="true" />
      <div className="container site-header__inner">
        <SmartLink href="#home" className="site-header__brand" aria-label={`${settings.site_name} — home`} onClick={close}>
          <Brand settings={settings} />
        </SmartLink>

        <nav className="main-nav" id="main-nav" aria-label="Main">
          <ul>
            {menu.map((item, i) => (
              <li key={item.id} style={{ '--i': i }}>
                <SmartLink href={item.url} className={active && item.url === `#${active}` ? 'is-active' : ''} onClick={close}>
                  {item.label}
                </SmartLink>
              </li>
            ))}
          </ul>
          {settings.header_cta_text && (
            <SmartLink href={settings.header_cta_link || '#contact'} className="btn btn--green main-nav__cta-mobile" onClick={close}>
              {settings.header_cta_text} <ArrowRight size={18} />
            </SmartLink>
          )}
        </nav>

        {settings.header_cta_text && (
          <SmartLink href={settings.header_cta_link || '#contact'} className="btn btn--green site-header__cta">
            {settings.header_cta_text} <ArrowRight size={18} />
          </SmartLink>
        )}

        <button
          type="button"
          className="menu-toggle"
          aria-expanded={open}
          aria-controls="main-nav"
          aria-label={open ? 'Close menu' : 'Open menu'}
          onClick={() => setOpen((o) => !o)}
        >
          {open ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>
      <div className="nav-backdrop" onClick={close} aria-hidden="true" />
    </header>
  )
}
