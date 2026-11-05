import { RefreshCw } from 'lucide-react'
import { useSite } from '../context/SiteContext'
import useSeo from '../hooks/useSeo'
import useSmoothScroll from '../hooks/useSmoothScroll'
import Loader from '../components/common/Loader'
import Header from '../components/layout/Header'
import Footer from '../components/layout/Footer'
import FloatingActions from '../components/layout/FloatingActions'
import Hero from '../sections/Hero'
import About from '../sections/About'
import Services from '../sections/Services'
import Gallery from '../sections/Gallery'
import Highlight from '../sections/Highlight'
import Events from '../sections/Events'
import Features from '../sections/Features'
import Testimonials from '../sections/Testimonials'
import Contact from '../sections/Contact'

export default function Home() {
  const { data, loading, error, reload } = useSite()
  useSeo(data?.settings)
  useSmoothScroll()

  if (loading && !data) return <Loader />
  if (error || !data) {
    return (
      <div className="page-error">
        <img src="/favicon.png" alt="" width="64" height="64" />
        <h1>We'll be right back</h1>
        <p>We couldn't load the page just now. Please check your connection and try again.</p>
        <button type="button" className="btn btn--green" onClick={reload}><RefreshCw size={18} /> Try again</button>
      </div>
    )
  }

  const { settings: s, menu, hero, services, gallery, highlights, events, features, testimonials, event_types, form_rules } = data

  return (
    <div className="site" style={s.theme_page_bg ? { '--page-bg': `url("${s.theme_page_bg}")` } : undefined}>
      <a href="#main" className="skip-link">Skip to content</a>
      <Header settings={s} menu={menu} />
      <main id="main">
        <Hero slides={hero} autoplaySeconds={s.hero_autoplay_seconds} />
        {s.about_enabled && <About s={s} />}
        {s.services_enabled && <Services s={s} items={services} />}
        {s.gallery_enabled && <Gallery s={s} items={gallery} />}
        {s.highlight_enabled && <Highlight s={s} items={highlights} />}
        {s.events_enabled && <Events s={s} items={events} />}
        {s.features_enabled && <Features s={s} items={features} />}
        {s.testimonials_enabled && <Testimonials s={s} items={testimonials} />}
        {s.contact_enabled && <Contact s={s} eventTypes={event_types} rules={form_rules} />}
      </main>
      <Footer settings={s} menu={menu} services={services} />
      <FloatingActions settings={s} />
    </div>
  )
}
