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
  const map = {
    received: 'Requested',
    assigned: 'Provider joined',
    quoted: 'Quotation shared',
    confirmed: 'Deal locked',
    in_progress: 'In progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return map[status] || String(status || '')
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

/**
 * Clear next-step guidance for customer or provider.
 */
export function bookingNextStep(booking, role = 'customer') {
  if (!booking) return null
  const status = booking.status
  const offerBy = booking.current_offer_by
  const money = formatMoney(booking.current_offer || booking.deal_amount)

  if (status === 'cancelled') {
    return { tone: 'warn', title: 'Booking cancelled', detail: 'This booking is closed and no further action is needed.' }
  }
  if (status === 'completed') {
    return { tone: 'done', title: 'Job completed', detail: 'Thanks — this booking cycle is finished.' }
  }

  if (role === 'customer') {
    if (status === 'received') {
      return { tone: 'info', title: 'Waiting for a provider', detail: 'Your request is live. A provider will accept it and share a quotation.' }
    }
    if (status === 'assigned') {
      return { tone: 'info', title: 'Provider joined', detail: 'Share or update your budget so the provider can quote back.' }
    }
    if (status === 'quoted' && offerBy === 'provider') {
      return { tone: 'warn', title: 'Action needed: accept quotation', detail: `Review ${money} and accept to lock the deal, or send a counter offer.` }
    }
    if (status === 'quoted' && offerBy === 'customer') {
      return { tone: 'info', title: 'Waiting on provider', detail: 'Your counter offer was sent. The provider will reply with an updated quotation.' }
    }
    if (status === 'confirmed') {
      return { tone: 'ok', title: 'Deal locked — chat is open', detail: 'The provider can start the job. Message them anytime from this page.' }
    }
    if (status === 'in_progress') {
      return { tone: 'warn', title: 'Action needed: mark completed', detail: 'When the work is done to your satisfaction, mark the job completed.' }
    }
  }

  if (role === 'provider') {
    if (status === 'received' && !booking.provider) {
      return { tone: 'warn', title: 'Open request', detail: 'Accept this booking to claim it, optionally with an initial quotation.' }
    }
    if (status === 'assigned') {
      return { tone: 'warn', title: 'Send a quotation', detail: 'Share your price so the customer can accept or counter.' }
    }
    if (status === 'quoted' && offerBy === 'provider') {
      return { tone: 'info', title: 'Waiting for customer', detail: `Your quotation ${money} is with the customer. They must accept before you can start.` }
    }
    if (status === 'quoted' && offerBy === 'customer') {
      return { tone: 'warn', title: 'Action needed: reply to counter', detail: `Customer offered ${money}. Send an updated quotation.` }
    }
    if (status === 'confirmed') {
      return { tone: 'warn', title: 'Action needed: start the job', detail: 'Deal is locked and chat is open. Start when you begin the work.' }
    }
    if (status === 'in_progress') {
      return { tone: 'info', title: 'Job in progress', detail: 'Only the customer can mark this completed when they are satisfied.' }
    }
  }

  return null
}
