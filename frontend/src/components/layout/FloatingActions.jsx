import { ArrowUp } from 'lucide-react'
import { useScrollY } from '../../hooks/useScroll'
import { telHref } from '../../utils/format'
import { InstagramIcon, PhoneIcon, WhatsAppIcon } from '../common/BrandIcons'

/** Floating quick-contact buttons — each one is switched on/off in Admin → Floating Buttons. */
export default function FloatingActions({ settings: s }) {
  const y = useScrollY()
  const wa = (s.contact_whatsapp || '').replace(/\D/g, '')
  const waText = s.float_whatsapp_message || `Hello ${s.site_name}, I would like to enquire about catering.`

  const buttons = [
    s.float_whatsapp_enabled && wa && {
      key: 'whatsapp', label: 'Chat on WhatsApp', icon: <WhatsAppIcon size={28} />,
      href: `https://wa.me/${wa}?text=${encodeURIComponent(waText)}`, external: true,
    },
    s.float_instagram_enabled && s.contact_instagram_url && {
      key: 'instagram', label: 'Follow us on Instagram', icon: <InstagramIcon size={24} />,
      href: s.contact_instagram_url, external: true,
    },
    s.float_call_enabled && s.contact_phone && {
      key: 'call', label: `Call ${s.contact_phone}`, icon: <PhoneIcon size={22} />,
      href: telHref(s.contact_phone),
    },
  ].filter(Boolean)

  if (!buttons.length && !s.float_top_enabled) return null

  return (
    <div className="floating-actions" aria-label="Quick contact">
      {buttons.map((b, i) => (
        <a
          key={b.key}
          className={`fab fab--${b.key}`}
          href={b.href}
          aria-label={b.label}
          style={{ '--i': i }}
          {...(b.external ? { target: '_blank', rel: 'noopener noreferrer' } : null)}
        >
          {b.icon}
          <span className="fab__label">{b.label}</span>
        </a>
      ))}
      {s.float_top_enabled && (
        <button
          type="button"
          className={`fab fab--top ${y > 600 ? '' : 'is-hidden'}`}
          onClick={() => (window.__lenis ? window.__lenis.scrollTo(0, { duration: 0.8 }) : window.scrollTo({ top: 0, behavior: 'smooth' }))}
          aria-label="Back to top"
          tabIndex={y > 600 ? 0 : -1}
        >
          <ArrowUp size={20} />
        </button>
      )}
    </div>
  )
}
