<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth, dashboardPathFor } from '../stores/auth'
import { ApiError } from '../api/client'

const router = useRouter()
const { registerCustomer } = useAuth()

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})
const errors = ref({})
const generalError = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''
  try {
    const user = await registerCustomer(form.value)
    router.push(dashboardPathFor(user))
  } catch (err) {
    if (err instanceof ApiError) {
      errors.value = err.errors
      if (!Object.keys(err.errors).length) generalError.value = err.message
    } else {
      generalError.value = 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card auth-card-wide">
      <RouterLink to="/" class="auth-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <span>TASKORA</span>
      </RouterLink>
      <p class="role-pill">Customer signup</p>
      <h1>Create customer account</h1>
      <p class="auth-sub">Book services, track jobs, and manage your Taskora dashboard.</p>

      <p v-if="generalError" class="auth-alert">{{ generalError }}</p>

      <form @submit.prevent="submit">
        <label>
          <span>Full name</span>
          <input v-model="form.name" type="text" autocomplete="name" required />
          <small v-if="errors.name">{{ errors.name[0] }}</small>
        </label>
        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" required />
          <small v-if="errors.email">{{ errors.email[0] }}</small>
        </label>
        <label>
          <span>Mobile <em>(optional)</em></span>
          <input v-model="form.phone" type="tel" autocomplete="tel" />
          <small v-if="errors.phone">{{ errors.phone[0] }}</small>
        </label>
        <label>
          <span>Password</span>
          <input v-model="form.password" type="password" autocomplete="new-password" required />
          <small v-if="errors.password">{{ errors.password[0] }}</small>
        </label>
        <label>
          <span>Confirm password</span>
          <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
        </label>
        <button type="submit" class="btn btn-gold" :disabled="loading">
          {{ loading ? 'Creating…' : 'Create customer account' }}
        </button>
      </form>

      <p class="auth-foot">
        Already have an account? <RouterLink to="/login">Sign in</RouterLink><br />
        Are you a professional?
        <RouterLink to="/register/provider">Provider signup</RouterLink>
      </p>
    </div>
  </div>
</template>
