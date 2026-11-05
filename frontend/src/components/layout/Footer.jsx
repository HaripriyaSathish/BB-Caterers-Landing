import { ArrowRight, Instagram, Mail, MapPin, PhoneCall } from 'lucide-react'
import SmartLink from '../common/SmartLink'
import Reveal from '../common/Reveal'
import { Brand } from './Header'
import { telHref } from '../../utils/format'
import useParallax from '../../hooks/useParallax'

export default function Footer({ settings: s, menu, services }) {
  const ctaRef = useParallax()
  return (
    <footer className="site-footer">
      {s.cta_enabled && (
        <section className="cta-band" aria-label="Call to action" ref={ctaRef}>
          <div className="container container--wide cta-band__inner">
            <Reveal as="h2" effect="fade-right" className="cta-band__title">
              {s.cta_line_1}
              {s.cta_line_2 && <em>{s.cta_line_2}</em>}
            </Reveal>
            {s.cta_button_text && (
              <Reveal effect="zoom-in" delay={200}>
                <SmartLink href={s.cta_button_link || '#contact'} className="btn btn--gold btn--lg">
                  {s.cta_button_text} <ArrowRight size={18} />
                </SmartLink>
              </Reveal>
            )}
          </div>
        </section>
      )}

      <div className="container container--wide footer-grid">
        <Reveal effect="fade-up" className="footer-col footer-col--brand">
          <Brand settings={s} light />
          {s.footer_about && <p>{s.footer_about}</p>}
        </Reveal>

        <Reveal effect="fade-up" delay={100} className="footer-col">
          <h3>Explore</h3>
          <ul>
            {menu.map((m) => (
              <li key={m.id}><SmartLink href={m.url}>{m.label}</SmartLink></li>
            ))}
          </ul>
        </Reveal>

        {services.length > 0 && (
          <Reveal effect="fade-up" delay={200} className="footer-col">
            <h3>Services</h3>
            <ul>
              {services.map((sv) => (
                <li key={sv.id}><SmartLink href="#services">{sv.title}</SmartLink></li>
              ))}
            </ul>
          </Reveal>
        )}

        <Reveal effect="fade-up" delay={300} className="footer-col">
          <h3>Get in touch</h3>
          <ul className="footer-contact">
            {s.contact_phone && <li><PhoneCall size={18} strokeWidth={1.6} /><a href={telHref(s.contact_phone)}>{s.contact_phone}</a></li>}
            {s.contact_email && <li><Mail size={18} strokeWidth={1.6} /><a href={`mailto:${s.contact_email}`}>{s.contact_email}</a></li>}
            {s.contact_instagram && (
              <li><Instagram size={18} strokeWidth={1.6} /><a href={s.contact_instagram_url || '#'} target="_blank" rel="noopener noreferrer">{s.contact_instagram}</a></li>
            )}
            {s.contact_address && <li className="footer-contact__address"><MapPin size={18} strokeWidth={1.6} /><span>{s.contact_address}</span></li>}
          </ul>
        </Reveal>
      </div>

      <div className="container container--wide footer-bottom">
        <span>{s.footer_copyright}</span>
        {(s.footer_credit || s.footer_credit_link_text) && (
          <span>
            {s.footer_credit}{' '}
            {s.footer_credit_link_text && (
              s.footer_credit_url
                ? <a className="footer-credit-link" href={s.footer_credit_url} target="_blank" rel="noopener noreferrer">{s.footer_credit_link_text}</a>
                : s.footer_credit_link_text
            )}
          </span>
        )}
      </div>
    </footer>
  )
}
