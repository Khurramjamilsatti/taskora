<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth, dashboardPathFor } from '../stores/auth'
import { ApiError } from '../api/client'
import { toastError, toastSuccess } from '../composables/useFeedback'

const router = useRouter()
const { registerProvider } = useAuth()

const categories = [
  'Home Services',
  'Construction & Renovation',
  'Architects & Interior Design',
  'Legal Services',
  'Chartered Accountants & Taxation',
  'IT Services & Software',
  'Tutors & Education',
  'Corporate & Business Services',
  'Facility Management',
  'HR & Recruitment',
  'Digital Marketing & Media',
  'Health & Wellness',
  'Event Management',
  'Logistics & Transport',
  'Security Services',
  'Insurance, Financial & Investment',
]

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  category: categories[0],
  subcategory: '',
  experience_years: '',
  service_areas: '',
  cnic: '',
  qualifications: '',
})
const errors = ref({})
const generalError = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''
  try {
    const payload = {
      ...form.value,
      experience_years: form.value.experience_years === '' ? null : Number(form.value.experience_years),
    }
    const user = await registerProvider(payload)
    toastSuccess('Welcome to Taskora', 'Your provider account is ready.')
    router.push(dashboardPathFor(user))
  } catch (err) {
    if (err instanceof ApiError) {
      errors.value = err.errors
      if (!Object.keys(err.errors).length) generalError.value = err.message
      toastError('Registration failed', err.message)
    } else {
      generalError.value = 'Something went wrong. Please try again.'
      toastError('Registration failed', generalError.value)
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
      <p class="role-pill provider">Provider signup</p>
      <h1>Join as a professional</h1>
      <p class="auth-sub">Registration → Verification → Training → Activation.</p>

      <p v-if="generalError" class="auth-alert">{{ generalError }}</p>

      <form @submit.prevent="submit" class="provider-form">
        <fieldset>
          <legend>Account</legend>
          <label>
            <span>Full name</span>
            <input v-model="form.name" type="text" required />
            <small v-if="errors.name">{{ errors.name[0] }}</small>
          </label>
          <label>
            <span>Email</span>
            <input v-model="form.email" type="email" required />
            <small v-if="errors.email">{{ errors.email[0] }}</small>
          </label>
          <label>
            <span>Mobile</span>
            <input v-model="form.phone" type="tel" required />
            <small v-if="errors.phone">{{ errors.phone[0] }}</small>
          </label>
          <label>
            <span>Password</span>
            <input v-model="form.password" type="password" required />
            <small v-if="errors.password">{{ errors.password[0] }}</small>
          </label>
          <label>
            <span>Confirm password</span>
            <input v-model="form.password_confirmation" type="password" required />
          </label>
        </fieldset>

        <fieldset>
          <legend>Professional details</legend>
          <label>
            <span>Main category</span>
            <select v-model="form.category" required>
              <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <small v-if="errors.category">{{ errors.category[0] }}</small>
          </label>
          <label>
            <span>Sub-category</span>
            <input v-model="form.subcategory" type="text" placeholder="e.g. Electrician — Wiring" />
          </label>
          <label>
            <span>Years of experience</span>
            <input v-model="form.experience_years" type="number" min="0" max="60" />
          </label>
          <label>
            <span>Service areas</span>
            <input v-model="form.service_areas" type="text" placeholder="Cities / zones covered" />
          </label>
          <label>
            <span>CNIC</span>
            <input v-model="form.cnic" type="text" placeholder="XXXXX-XXXXXXX-X" />
          </label>
          <label class="full">
            <span>Qualifications & certifications</span>
            <textarea v-model="form.qualifications" rows="3" />
          </label>
        </fieldset>

        <button type="submit" class="btn btn-gold" :disabled="loading">
          {{ loading ? 'Submitting…' : 'Submit provider application' }}
        </button>
      </form>

      <p class="auth-foot">
        Already have an account? <RouterLink to="/login">Sign in</RouterLink><br />
        Looking for services?
        <RouterLink to="/register/customer">Customer signup</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.provider-form fieldset {
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
  display: grid;
  gap: 14px;
}
.provider-form legend {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--green);
  padding: 0 6px;
}
.provider-form select,
.provider-form textarea {
  padding: 12px 14px;
  border: 1.5px solid var(--line);
  border-radius: 6px;
  font-size: 15px;
  font-family: inherit;
  background: var(--white);
}
.provider-form .full { grid-column: 1 / -1; }
</style>
