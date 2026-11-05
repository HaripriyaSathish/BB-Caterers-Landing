/** Brand glyphs for the floating buttons (drawn as simple SVGs, fill = currentColor). */

export function WhatsAppIcon({ size = 26 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
      {/* speech bubble with tail */}
      <path d="M16 3.2C8.94 3.2 3.2 8.83 3.2 15.75c0 2.4.7 4.72 2 6.72L3.4 28.8l6.53-1.7a13 13 0 0 0 6.07 1.5c7.06 0 12.8-5.63 12.8-12.55S23.06 3.2 16 3.2Zm0 22.96c-1.94 0-3.84-.52-5.5-1.5l-.4-.23-3.87 1 1.03-3.7-.26-.4a10.2 10.2 0 0 1-1.6-5.53c0-5.72 4.77-10.38 10.6-10.38s10.6 4.66 10.6 10.38S21.84 26.16 16 26.16Z" />
      {/* handset */}
      <path d="M21.82 18.62c-.32-.16-1.9-.92-2.2-1.03-.29-.1-.5-.16-.72.16-.21.31-.82 1.03-1.01 1.24-.19.2-.37.23-.69.08a8.7 8.7 0 0 1-2.58-1.57 9.6 9.6 0 0 1-1.79-2.18c-.19-.31-.02-.48.14-.64.15-.14.32-.36.48-.55.16-.18.21-.31.32-.52.1-.2.05-.39-.03-.54-.08-.16-.71-1.69-.98-2.31-.25-.6-.52-.52-.71-.53h-.61c-.21 0-.56.08-.85.39-.29.31-1.12 1.08-1.12 2.62s1.15 3.04 1.3 3.25c.17.2 2.27 3.4 5.5 4.76.77.33 1.37.52 1.84.67.77.24 1.47.2 2.03.12.62-.09 1.9-.76 2.17-1.5.27-.73.27-1.36.19-1.5-.08-.13-.29-.2-.61-.36Z" />
    </svg>
  )
}

export function InstagramIcon({ size = 24 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
      <rect x="3" y="3" width="18" height="18" rx="5.2" />
      <circle cx="12" cy="12" r="4.2" />
      <circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none" />
    </svg>
  )
}

/** Filled envelope with a cut-out fold (Figma contact icon). */
export function MailFilledIcon({ size = 20 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" aria-hidden="true">
      <rect x="2.5" y="5" width="19" height="14" rx="2" fill="currentColor" />
      <path d="m3.6 6.6 8.4 6.1 8.4-6.1" fill="none" stroke="var(--icon-cut, #0d3a24)" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  )
}

/** Small gold lotus used on the enquiry card. */
export function LotusIcon({ size = 30 }) {
  return (
    <svg width={size} height={size * 0.62} viewBox="0 0 40 25" fill="currentColor" aria-hidden="true">
      <path d="M20 0c3.2 4.2 4.6 9.6 3.6 15.2-.6 3.4-2 6.3-3.6 8.3-1.6-2-3-4.9-3.6-8.3C15.4 9.6 16.8 4.2 20 0Z" />
      <path d="M2 9.5c6.4-.3 11.9 2.4 15.5 7.2 1.2 1.6 2 3.4 2.5 5.3-4.7.2-9.4-1.5-12.9-5A15.7 15.7 0 0 1 2 9.5Zm36 0a15.7 15.7 0 0 1-5.1 7.5c-3.5 3.5-8.2 5.2-12.9 5 .5-1.9 1.3-3.7 2.5-5.3C26.1 11.9 31.6 9.2 38 9.5Z" opacity=".85" />
    </svg>
  )
}

export function PhoneIcon({ size = 22 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z" />
    </svg>
  )
}
