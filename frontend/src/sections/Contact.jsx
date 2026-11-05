import { useMemo, useRef, useState } from 'react'
import { AlertCircle, ArrowRight, CheckCircle2, Loader2, MapPin } from 'lucide-react'
import { InstagramIcon, LotusIcon, MailFilledIcon, PhoneIcon } from '../components/common/BrandIcons'
import Reveal from '../components/common/Reveal'
import SectionHeading from '../components/common/SectionHeading'
import { sendEnquiry } from '../services/api'
import { FIELDS, validateAll } from '../utils/validation'
import { rich, telHref } from '../utils/format'

const EMPTY = { name: '', email: '', phone: '', event_date: '', event_type: '', guests: '', message: '', website: '' }

function Field({ id, label, error, touched, children, full, hint }) {
  const show = touched && error
  return (
    <div className={`form-field ${full ? 'form-field--full' : ''} ${show ? 'has-error' : ''} ${touched && !error ? 'is-valid' : ''}`}>
      <label htmlFor={id}>{label}</label>
      {children}
      {show ? (
        <small className="form-field__error" id={`${id}-error`} role="alert"><AlertCircle size={14} /> {error}</small>
      ) : (
        hint && <small className="form-field__hint">{hint}</small>
      )}
    </div>
  )
}

export default function Contact({ s, eventTypes, rules }) {
  const [values, setValues] = useState(EMPTY)
  const [touched, setTouched] = useState({})
  const [serverErrors, setServerErrors] = useState({})
  const [status, setStatus] = useState({ state: 'idle', message: '' }) // idle | sending | success | error
  const formRef = useRef(null)

  const clientErrors = useMemo(() => validateAll(values, rules, eventTypes), [values, rules, eventTypes])
  const errors = { ...clientErrors, ...serverErrors }

  const onChange = (e) => {
    const { name, value } = e.target
    let v = value
    if (name === 'guests') v = value.replace(/\D/g, '').slice(0, 6)
    if (name === 'phone') v = value.replace(/[^\d+\s()-]/g, '').slice(0, 16)
    setValues((p) => ({ ...p, [name]: v }))
    if (serverErrors[name]) setServerErrors((prev) => { const next = { ...prev }; delete next[name]; return next })
    if (status.state === 'error') setStatus({ state: 'idle', message: '' })
  }
  const onBlur = (e) => setTouched((t) => ({ ...t, [e.target.name]: true }))

  const onSubmit = async (e) => {
    e.preventDefault()
    if (status.state === 'sending') return
    setTouched(Object.fromEntries(FIELDS.map((f) => [f, true])))
    const errs = validateAll(values, rules, eventTypes)
    if (Object.keys(errs).length) {
      setStatus({ state: 'error', message: 'Please correct the highlighted fields.' })
      const first = FIELDS.find((f) => errs[f])
      formRef.current?.querySelector(`[name="${first}"]`)?.focus()
      return
    }
    setStatus({ state: 'sending', message: '' })
    try {
      const res = await sendEnquiry({ ...values, guests: Number(values.guests) })
      setStatus({ state: 'success', message: res.message || s.contact_success_message })
      setValues(EMPTY)
      setTouched({})
    } catch (err) {
      setServerErrors(err.errors || {})
      setStatus({ state: 'error', message: err.message })
      const first = FIELDS.find((f) => err.errors?.[f])
      if (first) formRef.current?.querySelector(`[name="${first}"]`)?.focus()
    }
  }

  const aria = (name) => ({
    id: `enq-${name}`,
    name,
    value: values[name],
    onChange,
    onBlur,
    'aria-required': true,
    'aria-invalid': touched[name] && errors[name] ? true : undefined,
    'aria-describedby': touched[name] && errors[name] ? `enq-${name}-error` : undefined,
  })
  const r = rules || {}

  return (
    <section id="contact" className="section contact">
      <div className="container contact__grid">
        <div className="contact__info">
          <SectionHeading eyebrow={s.contact_eyebrow} title={s.contact_title} align="left" italic eyebrowStyle="green" />
          {s.contact_text && <Reveal as="p" effect="fade-up" delay={150} className="contact__text">{rich(s.contact_text)}</Reveal>}
          <ul className="contact__list">
            {s.contact_phone && (
              <Reveal as="li" effect="fade-right" delay={200}>
                <span className="contact__icon"><PhoneIcon size={20} /></span>
                <a href={telHref(s.contact_phone)}>{s.contact_phone}</a>
              </Reveal>
            )}
            {s.contact_email && (
              <Reveal as="li" effect="fade-right" delay={300}>
                <span className="contact__icon"><MailFilledIcon size={21} /></span>
                <a href={`mailto:${s.contact_email}`}>{s.contact_email}</a>
              </Reveal>
            )}
            {s.contact_instagram && (
              <Reveal as="li" effect="fade-right" delay={400}>
                <span className="contact__icon"><InstagramIcon size={20} /></span>
                <a href={s.contact_instagram_url || '#'} target="_blank" rel="noopener noreferrer">{s.contact_instagram}</a>
              </Reveal>
            )}
            {s.contact_address && (
              <Reveal as="li" effect="fade-right" delay={500}>
                <span className="contact__icon"><MapPin size={20} /></span>
                <span>{s.contact_address}</span>
              </Reveal>
            )}
          </ul>
        </div>

        <Reveal effect="fade-left" delay={100} className="contact__card">
          <span className="contact__corner" aria-hidden="true" />
          <span className="contact__arc" aria-hidden="true" />
          <span className="contact__lotus" aria-hidden="true"><LotusIcon size={32} /></span>

          {status.state === 'success' ? (
            <div className="form-success" role="status">
              <CheckCircle2 size={64} strokeWidth={1.5} />
              <h3>Thank you!</h3>
              <p>{status.message}</p>
              <button type="button" className="btn btn--green" onClick={() => setStatus({ state: 'idle', message: '' })}>
                Send another enquiry
              </button>
            </div>
          ) : (
            <form ref={formRef} className="enquiry-form" onSubmit={onSubmit} noValidate>
              <Field id="enq-name" label="Full Name" error={errors.name} touched={touched.name}>
                <input type="text" placeholder="Your name" autoComplete="name" maxLength={r.name?.max ?? 60} {...aria('name')} />
              </Field>
              <Field id="enq-email" label="Email Address" error={errors.email} touched={touched.email}>
                <input type="email" placeholder="you@example.com" autoComplete="email" inputMode="email" maxLength={r.email?.max ?? 150} {...aria('email')} />
              </Field>
              <Field id="enq-phone" label="Phone Number" error={errors.phone} touched={touched.phone}>
                <input type="tel" placeholder="+91" autoComplete="tel" inputMode="tel" {...aria('phone')} />
              </Field>
              <Field id="enq-event_date" label="Event Date" error={errors.event_date} touched={touched.event_date}>
                <input type="date" min={r.event_date?.min} max={r.event_date?.max} {...aria('event_date')} />
              </Field>
              <Field id="enq-event_type" label="Event Type" error={errors.event_type} touched={touched.event_type}>
                <select {...aria('event_type')}>
                  <option value="">Select</option>
                  {eventTypes.map((t) => <option key={t} value={t}>{t}</option>)}
                </select>
              </Field>
              <Field
                id="enq-guests"
                label="Number of Guests"
                error={errors.guests}
                touched={touched.guests}
              >
                <input type="text" inputMode="numeric" placeholder="e.g. 150" {...aria('guests')} />
              </Field>
              <Field id="enq-message" label="Message" error={errors.message} touched={touched.message} full>
                <textarea rows={3} placeholder="Tell us about your menu ideas" maxLength={r.message?.max ?? 1000} {...aria('message')} />
              </Field>

              {/* Honeypot — hidden from people, bots fill it */}
              <div className="hp" aria-hidden="true">
                <label htmlFor="enq-website">Website</label>
                <input id="enq-website" name="website" tabIndex={-1} autoComplete="off" value={values.website} onChange={onChange} />
              </div>

              {status.state === 'error' && (
                <p className="form-alert" role="alert"><AlertCircle size={18} /> {status.message}</p>
              )}

              <button type="submit" className="btn btn--green btn--lg enquiry-form__submit" disabled={status.state === 'sending'}>
                {status.state === 'sending' ? (
                  <><Loader2 size={18} className="spin" /> Sending…</>
                ) : (
                  <>{s.contact_button_text || 'Request a Catering Quote'} <ArrowRight size={18} /></>
                )}
              </button>
            </form>
          )}
        </Reveal>
      </div>
    </section>
  )
}
