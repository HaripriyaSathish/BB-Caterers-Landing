import { Link } from 'react-router-dom'
import { ArrowLeft } from 'lucide-react'

export default function NotFound() {
  return (
    <div className="page-error">
      <img src="/favicon.png" alt="" width="64" height="64" />
      <p className="eyebrow">Error 404</p>
      <h1>This page isn't on the menu</h1>
      <p>The page you are looking for doesn't exist or has moved.</p>
      <Link to="/" className="btn btn--green"><ArrowLeft size={18} /> Back to home</Link>
    </div>
  )
}
