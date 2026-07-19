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

export function calculateEstimate(payload) {
  return request('/estimate', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function apiRegister(payload) {
  return request('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
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
