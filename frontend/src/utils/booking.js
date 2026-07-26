export const BOOKING_STATUSES = [
  'received',
  'assigned',
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
  if (s === 'completed') return 'ok'
  if (s === 'cancelled') return 'bad'
  if (['received', 'assigned', 'in_progress'].includes(s)) return 'warn'
  return ''
}

export function bookingTimeline(booking) {
  return [
    { key: 'received', label: 'Requested', at: booking?.created_at, done: true },
    {
      key: 'assigned',
      label: 'Provider accepted',
      at: booking?.accepted_at,
      done: ['assigned', 'in_progress', 'completed'].includes(booking?.status),
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
