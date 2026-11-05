import { createLucideIcon } from 'lucide-react'

/*
 * Icons drawn to match the Figma design where Lucide has no exact equivalent.
 * The same shapes are registered in the admin (backend/admin/assets/admin.js → CUSTOM_ICONS).
 */
export const AwardStar = createLucideIcon('AwardStar', [
  ['circle', { cx: '12', cy: '8', r: '6', key: 'a1' }],
  ['path', { d: 'm12 5.4.8 1.6 1.8.3-1.3 1.2.3 1.8-1.6-.85-1.6.85.3-1.8L9.4 7.3l1.8-.3z', key: 'a2' }],
  ['path', { d: 'm15.48 12.89 1.51 8.53a.5.5 0 0 1-.81.47l-3.58-2.69a1 1 0 0 0-1.2 0l-3.58 2.69a.5.5 0 0 1-.81-.47l1.51-8.53', key: 'a3' }],
])

export const Rings = createLucideIcon('Rings', [
  ['circle', { cx: '8.5', cy: '14.5', r: '5.5', key: 'r1' }],
  ['circle', { cx: '15.5', cy: '14.5', r: '5.5', key: 'r2' }],
  ['path', { d: 'm8.5 6.5-1.5-2 1.5-2 1.5 2z', key: 'r3' }],
])

export const Laddoo = createLucideIcon('Laddoo', [
  ['circle', { cx: '12', cy: '7', r: '3.5', key: 'l1' }],
  ['circle', { cx: '7.5', cy: '14', r: '3.5', key: 'l2' }],
  ['circle', { cx: '16.5', cy: '14', r: '3.5', key: 'l3' }],
  ['path', { d: 'M3 20.5h18', key: 'l4' }],
])
