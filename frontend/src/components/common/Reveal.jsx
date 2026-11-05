import useReveal from '../../hooks/useReveal'

/**
 * Scroll-triggered animation wrapper.
 * effect: fade-up | fade-down | fade-left | fade-right | zoom-in | zoom-out | blur-in | flip-up | rise
 * delay in ms. Use `as` to change the element.
 */
export default function Reveal({ as: Tag = 'div', effect = 'fade-up', delay = 0, duration, className = '', style, children, ...rest }) {
  const [ref, visible] = useReveal()
  return (
    <Tag
      ref={ref}
      className={`reveal reveal--${effect} ${visible ? 'is-visible' : ''} ${className}`}
      style={{ '--reveal-delay': `${delay}ms`, ...(duration ? { '--reveal-duration': `${duration}ms` } : null), ...style }}
      {...rest}
    >
      {children}
    </Tag>
  )
}
