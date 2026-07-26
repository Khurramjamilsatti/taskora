<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth, dashboardPathFor } from '../stores/auth'
import { ApiError } from '../api/client'
import { toastError, toastSuccess } from '../composables/useFeedback'

const router = useRouter()
const route = useRoute()
const { login } = useAuth()

const form = ref({
  email: '',
  password: '',
  role: route.query.as === 'provider' ? 'provider' : 'customer',
})
const errors = ref({})
const generalError = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''
  try {
    const user = await login(form.value)
    toastSuccess('Welcome back', user.name ? `Signed in as ${user.name}` : 'You are signed in.')
    const redirect = route.query.redirect || dashboardPathFor(user)
    router.push(redirect)
  } catch (err) {
    if (err instanceof ApiError) {
      errors.value = err.errors
      if (!Object.keys(err.errors).length) generalError.value = err.message
      toastError('Sign in failed', err.message)
    } else {
      generalError.value = 'Something went wrong. Please try again.'
      toastError('Sign in failed', generalError.value)
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <RouterLink to="/" class="auth-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <span>TASKORA</span>
      </RouterLink>
      <h1>Sign in</h1>
      <p class="auth-sub">Access your customer or provider dashboard.</p>

      <p v-if="generalError" class="auth-alert">{{ generalError }}</p>

      <form @submit.prevent="submit">
        <label>
          <span>I am signing in as</span>
          <select v-model="form.role">
            <option value="customer">Customer</option>
            <option value="provider">Provider</option>
          </select>
        </label>
        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" required />
          <small v-if="errors.email">{{ errors.email[0] }}</small>
        </label>
        <label>
          <span>Password</span>
          <input v-model="form.password" type="password" autocomplete="current-password" required />
          <small v-if="errors.password">{{ errors.password[0] }}</small>
        </label>
        <button type="submit" class="btn btn-gold" :disabled="loading">
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>

      <p class="auth-foot">
        New customer? <RouterLink to="/register/customer">Create account</RouterLink><br />
        New professional? <RouterLink to="/register/provider">Provider signup</RouterLink>
      </p>
    </div>
  </div>
</template>
