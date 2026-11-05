/**
 * Client-side mirror of backend/core/validation.php.
 * Limits come from the API (form_rules) so both sides always agree;
 * the server still re-checks everything.
 */

// Control characters + zero-width / bidi marks (built from code points so the source stays plain ASCII)
const CONTROL = new RegExp('[' + [[0x00, 0x08], [0x0b, 0x0c], [0x0e, 0x1f], [0x7f, 0x7f], [0x200b, 0x200f], [0x202a, 0x202e], [0x2060, 0x2064], [0xfeff, 0xfeff]]
  .map(([a, b]) => String.fromCharCode(a) + '-' + String.fromCharCode(b)).join('') + ']')
const NAME = /^\p{L}[\p{L}\p{M} .'-]*\p{L}\.?$/u
const EMAIL = /^[a-z0-9._%+-]+@[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,24}$/

const fmt = (d) => d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })

export function normalisePhone(raw = '') {
  let d = raw.replace(/\D/g, '')
  if (d.length === 12 && d.startsWith('91')) d = d.slice(2)
  else if (d.length === 11 && d.startsWith('0')) d = d.slice(1)
  return d
}

export function validateField(name, value, rules, eventTypes = []) {
  const v = typeof value === 'string' ? value.trim().replace(name === 'message' ? /[ \t]+/g : /\s+/g, ' ') : value
  const r = rules?.[name] || {}

  switch (name) {
    case 'name': {
      if (!v) return 'Please enter your full name.'
      if (CONTROL.test(v)) return 'Name contains invalid characters.'
      const min = r.min ?? 2, max = r.max ?? 60
      if (v.length < min || v.length > max) return `Name must be ${min}–${max} characters.`
      if (!NAME.test(v)) return 'Name can contain only letters, spaces, dots, apostrophes and hyphens.'
      if (/[ .'-]{2,}/.test(v)) return 'Please enter a valid name.'
      return ''
    }
    case 'email': {
      const e = (v || '').toLowerCase()
      if (!e) return 'Please enter your email address.'
      if (e.length > (r.max ?? 150) || !EMAIL.test(e) || e.includes('..')) return 'Please enter a valid email address (e.g. name@example.com).'
      return ''
    }
    case 'phone': {
      if (!v) return 'Please enter your phone number.'
      if (!/^[0-9+\s()-]+$/.test(v)) return 'Phone number can contain only digits, spaces, + and -.'
      const d = normalisePhone(v)
      if (!/^[6-9]\d{9}$/.test(d)) return 'Please enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.'
      if (/^(\d)\1{9}$/.test(d)) return 'Please enter a real phone number.'
      return ''
    }
    case 'event_date': {
      if (!v) return 'Please choose your event date.'
      if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return 'Please choose a valid date.'
      const [y, m, d] = v.split('-').map(Number)
      const date = new Date(y, m - 1, d)
      if (date.getFullYear() !== y || date.getMonth() !== m - 1 || date.getDate() !== d) return 'Please choose a valid date.'
      const today = new Date(); today.setHours(0, 0, 0, 0)
      const minD = new Date(today); minD.setDate(minD.getDate() + (r.min_days_ahead ?? 1))
      const maxD = new Date(today); maxD.setDate(maxD.getDate() + (r.max_days_ahead ?? 730))
      if (date < minD) return `Event date must be from ${fmt(minD)} onwards.`
      if (date > maxD) return `We take bookings up to ${fmt(maxD)}.`
      return ''
    }
    case 'event_type':
      if (!v) return 'Please select the type of event.'
      if (eventTypes.length && !eventTypes.includes(v)) return 'Please select an event type from the list.'
      return ''
    case 'guests': {
      const min = r.min ?? 10, max = r.max ?? 5000
      if (v === '' || v == null) return 'Please enter the number of guests.'
      if (!/^[1-9]\d{0,5}$/.test(String(v))) return 'Number of guests must be a whole number.'
      if (+v < min || +v > max) return `We cater for ${min} to ${max.toLocaleString('en-IN')} guests.`
      return ''
    }
    case 'message': {
      const min = r.min ?? 10, max = r.max ?? 1000
      if (!v) return 'Please tell us a little about your event or menu ideas.'
      if (CONTROL.test(v)) return 'Message contains invalid characters.'
      if (v.length < min) return `Message must be at least ${min} characters.`
      if (v.length > max) return `Message must be under ${max} characters.`
      if (/<\s*\/?\s*[a-z!][^>]*>/i.test(v)) return 'HTML or code is not allowed in the message.'
      if ((v.match(/(https?:\/\/|www\.)/gi) || []).length > 1) return 'Please do not include links in your message.'
      if (/(.)\1{14,}/u.test(v)) return 'Please enter a meaningful message.'
      return ''
    }
    default:
      return ''
  }
}

export const FIELDS = ['name', 'email', 'phone', 'event_date', 'event_type', 'guests', 'message']

export function validateAll(values, rules, eventTypes) {
  const errors = {}
  FIELDS.forEach((f) => {
    const msg = validateField(f, values[f], rules, eventTypes)
    if (msg) errors[f] = msg
  })
  return errors
}
