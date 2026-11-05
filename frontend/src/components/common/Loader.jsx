export default function Loader({ logo }) {
  return (
    <div className="page-loader" role="status" aria-live="polite">
      <div className="page-loader__inner">
        {logo ? <img src={logo} alt="" /> : <img src="/favicon.png" alt="" />}
        <span className="page-loader__bar" />
        <span className="sr-only">Loading…</span>
      </div>
    </div>
  )
}
