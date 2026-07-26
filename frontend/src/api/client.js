const API_BASE = import.meta.env.VITE_API_URL || '/api'

const TOKEN_KEY = 'taskora_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
  }
}

export class ApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

async function request(path, options = {}) {
  const token = getToken()
  const response = await fetch(`${API_BASE}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
    ...options,
  })

  if (response.status === 204) {
    return null
  }

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new ApiError(
      data.message || `API request failed: ${response.status}`,
      response.status,
      data.errors || {},
    )
  }

  return data
}

export function fetchSiteData() {
  return request('/site')
}

export function fetchCatalogue() {
  return request('/catalogue')
}

export function calculateEstimate(payload) {
  return request('/estimate', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiRegisterCustomer(payload) {
  return request('/register/customer', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiRegisterProvider(payload) {
  return request('/register/provider', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiRegister(payload) {
  const role = payload.role || 'customer'
  return role === 'provider'
    ? apiRegisterProvider(payload)
    : apiRegisterCustomer(payload)
}

export function apiLogin(payload) {
  return request('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiLogout() {
  return request('/logout', { method: 'POST' })
}

export function apiMe() {
  return request('/me')
}

export function apiSubmitForm(type, payload) {
  return request(`/forms/${type}`, {
    method: 'POST',
    body: JSON.stringify({ payload }),
  })
}

export function apiMyForms(type) {
  const qs = type ? `?type=${encodeURIComponent(type)}` : ''
  return request(`/my-forms${qs}`)
}

export function apiCreateBooking(payload) {
  return request('/bookings', {
    method: 'POST',
    body: JSON.stringify({ payload }),
  })
}

export function apiMyBookings() {
  return request('/bookings')
}

export function apiGetBooking(id) {
  return request(`/bookings/${id}`)
}

export function apiCancelBooking(id, note) {
  return request(`/bookings/${id}/cancel`, {
    method: 'POST',
    body: JSON.stringify(note ? { note } : {}),
  })
}

export function apiBookingRequests(params = {}) {
  const query = new URLSearchParams()
  if (params.q) query.set('q', params.q)
  if (params.category) query.set('category', params.category)
  const qs = query.toString()
  return request(`/booking-requests${qs ? `?${qs}` : ''}`)
}

export function apiProviderJobs(status) {
  const qs = status ? `?status=${encodeURIComponent(status)}` : ''
  return request(`/provider/jobs${qs}`)
}

export function apiAcceptBooking(id, payload = {}) {
  return request(`/bookings/${id}/accept`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiProposeBudget(id, amount, note) {
  return request(`/bookings/${id}/propose`, {
    method: 'POST',
    body: JSON.stringify({ amount, ...(note ? { note } : {}) }),
  })
}

export function apiAcceptQuote(id, note) {
  return request(`/bookings/${id}/accept-quote`, {
    method: 'POST',
    body: JSON.stringify(note ? { note } : {}),
  })
}

export function apiStartBooking(id, note) {
  return request(`/bookings/${id}/start`, {
    method: 'POST',
    body: JSON.stringify(note ? { note } : {}),
  })
}

export function apiCompleteBooking(id, note) {
  return request(`/bookings/${id}/complete`, {
    method: 'POST',
    body: JSON.stringify(note ? { note } : {}),
  })
}

export function apiBookingMessages(id) {
  return request(`/bookings/${id}/messages`)
}

export function apiSendBookingMessage(id, body) {
  return request(`/bookings/${id}/messages`, {
    method: 'POST',
    body: JSON.stringify({ body }),
  })
}

export function apiNotifications() {
  return request('/notifications')
}

export function apiMarkNotificationRead(id) {
  return request(`/notifications/${id}/read`, { method: 'POST' })
}

export function apiMarkAllNotificationsRead() {
  return request('/notifications/read-all', { method: 'POST' })
}

export function apiUpdateProfile(payload) {
  return request('/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function apiChangePassword(payload) {
  return request('/profile/password', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}
