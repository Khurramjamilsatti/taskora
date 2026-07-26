import { computed, reactive } from 'vue'
import {
  apiLogin,
  apiLogout,
  apiMe,
  apiRegisterCustomer,
  apiRegisterProvider,
  getToken,
  setToken,
} from '../api/client'

const state = reactive({
  user: null,
  token: getToken(),
  ready: false,
})

export function dashboardPathFor(user) {
  if (user?.role === 'provider') {
    return '/dashboard/provider'
  }
  return '/dashboard/customer'
}

export function useAuth() {
  const isAuthenticated = computed(() => !!state.token && !!state.user)
  const isCustomer = computed(() => state.user?.role === 'customer')
  const isProvider = computed(() => state.user?.role === 'provider')

  async function login(credentials) {
    const data = await apiLogin(credentials)
    setToken(data.token)
    state.token = data.token
    state.user = data.user
    return data.user
  }

  async function registerCustomer(payload) {
    const data = await apiRegisterCustomer(payload)
    setToken(data.token)
    state.token = data.token
    state.user = data.user
    return data.user
  }

  async function registerProvider(payload) {
    const data = await apiRegisterProvider(payload)
    setToken(data.token)
    state.token = data.token
    state.user = data.user
    return data.user
  }

  async function logout() {
    try {
      if (state.token) {
        await apiLogout()
      }
    } catch {
      // Ignore network/errors on logout; clear locally regardless.
    } finally {
      setToken(null)
      state.token = null
      state.user = null
    }
  }

  async function fetchUser() {
    if (!state.token) {
      state.user = null
      return null
    }
    try {
      state.user = await apiMe()
    } catch {
      setToken(null)
      state.token = null
      state.user = null
    }
    return state.user
  }

  async function init() {
    if (state.ready) {
      return
    }
    await fetchUser()
    state.ready = true
  }

  return {
    state,
    isAuthenticated,
    isCustomer,
    isProvider,
    login,
    registerCustomer,
    registerProvider,
    logout,
    fetchUser,
    init,
    dashboardPathFor,
  }
}
