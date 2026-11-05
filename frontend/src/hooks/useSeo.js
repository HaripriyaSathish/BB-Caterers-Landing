import { useEffect } from 'react'

function setMeta(attr, key, content) {
  if (!content) return
  let el = document.head.querySelector(`meta[${attr}="${key}"]`)
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute(attr, key)
    document.head.appendChild(el)
  }
  el.setAttribute('content', content)
}

function setLink(rel, href) {
  if (!href) return
  let el = document.head.querySelector(`link[rel="${rel}"]`)
  if (!el) {
    el = document.createElement('link')
    el.rel = rel
    document.head.appendChild(el)
  }
  el.href = href
}

/** Applies SEO title, description, keywords, social tags and favicon from admin settings. */
export default function useSeo(settings) {
  useEffect(() => {
    if (!settings) return
    const title = settings.meta_title || settings.site_name
    if (title) document.title = title
    setMeta('name', 'description', settings.meta_description)
    setMeta('name', 'keywords', settings.meta_keywords)
    setMeta('property', 'og:title', title)
    setMeta('property', 'og:description', settings.meta_description)
    setMeta('property', 'og:image', settings.og_image)
    setMeta('property', 'og:type', 'website')
    setMeta('property', 'og:url', window.location.origin + window.location.pathname)
    setMeta('name', 'twitter:card', 'summary_large_image')
    setLink('icon', settings.favicon)
    setLink('apple-touch-icon', settings.apple_touch_icon)
  }, [settings])
}
