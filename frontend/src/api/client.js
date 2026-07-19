const API_BASE = import.meta.env.VITE_API_URL || '/api'

async function request(path, options = {}) {
  const response = await fetch(`${API_BASE}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  })

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`)
  }

  return response.json()
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
