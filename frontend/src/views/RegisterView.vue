<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { ApiError } from '../api/client'

const router = useRouter()
const { register } = useAuth()

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref({})
const generalError = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''
  try {
    await register(form.value)
    router.push('/dashboard')
  } catch (err) {
    if (err instanceof ApiError) {
      errors.value = err.errors
      if (!Object.keys(err.errors).length) {
        generalError.value = err.message
      }
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
    <div class="auth-card">
      <RouterLink to="/" class="auth-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <span>TASKORA</span>
      </RouterLink>
      <h1>Create account</h1>
      <p class="auth-sub">Start managing your Taskora services.</p>

      <p v-if="generalError" class="auth-alert">{{ generalError }}</p>

      <form @submit.prevent="submit">
        <label>
          <span>Name</span>
          <input v-model="form.name" type="text" autocomplete="name" required />
          <small v-if="errors.name">{{ errors.name[0] }}</small>
        </label>
        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" required />
          <small v-if="errors.email">{{ errors.email[0] }}</small>
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
          {{ loading ? 'Creating…' : 'Create account' }}
        </button>
      </form>

      <p class="auth-foot">
        Already have an account? <RouterLink to="/login">Sign in</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  background: linear-gradient(160deg, var(--green-deep), var(--black));
}
.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--white);
  border-radius: 12px;
  padding: 40px 34px;
  box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
}
.auth-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  letter-spacing: 0.04em;
  margin-bottom: 26px;
}
.auth-brand img { width: 34px; height: 34px; }
.auth-card h1 { font-size: 26px; margin-bottom: 6px; }
.auth-sub { color: rgba(18, 32, 26, 0.65); margin-bottom: 22px; font-size: 14px; }
form { display: flex; flex-direction: column; gap: 16px; }
label { display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; }
input {
  padding: 12px 14px;
  border: 1.5px solid var(--line);
  border-radius: 6px;
  font-size: 15px;
  font-family: inherit;
}
input:focus { outline: none; border-color: var(--green); }
small { color: #c0392b; font-weight: 500; }
button { justify-content: center; margin-top: 6px; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.auth-alert {
  background: #fdecea;
  color: #b03024;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 16px;
}
.auth-foot { margin-top: 20px; font-size: 14px; text-align: center; }
.auth-foot a { color: var(--green); font-weight: 600; }
</style>
