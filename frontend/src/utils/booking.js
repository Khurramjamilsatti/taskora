export const BOOKING_STATUSES = [
  'received',
  'assigned',
  'quoted',
  'confirmed',
  'in_progress',
  'completed',
  'cancelled',
]

export function statusLabel(status) {
  return String(status || '')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
}

export function statusClass(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'completed' || s === 'confirmed') return 'ok'
  if (s === 'cancelled') return 'bad'
  if (['received', 'assigned', 'quoted', 'in_progress'].includes(s)) return 'warn'
  return ''
}

export function formatMoney(amount) {
  if (amount === null || amount === undefined || amount === '') return '—'
  const n = Number(amount)
  if (Number.isNaN(n)) return '—'
  return `PKR ${n.toLocaleString()}`
}

export function bookingTimeline(booking) {
  return [
    { key: 'received', label: 'Requested', at: booking?.created_at, done: true },
    {
      key: 'assigned',
      label: 'Provider joined',
      at: booking?.accepted_at,
      done: ['assigned', 'quoted', 'confirmed', 'in_progress', 'completed'].includes(booking?.status),
    },
    {
      key: 'quoted',
      label: 'Quotation',
      at: booking?.quoted_at,
      done: ['quoted', 'confirmed', 'in_progress', 'completed'].includes(booking?.status),
    },
    {
      key: 'confirmed',
      label: 'Deal accepted',
      at: booking?.deal_accepted_at,
      done: ['confirmed', 'in_progress', 'completed'].includes(booking?.status),
    },
    {
      key: 'in_progress',
      label: 'In progress',
      at: booking?.started_at,
      done: ['in_progress', 'completed'].includes(booking?.status),
    },
    {
      key: 'completed',
      label: 'Completed',
      at: booking?.completed_at,
      done: booking?.status === 'completed',
    },
  ]
}
