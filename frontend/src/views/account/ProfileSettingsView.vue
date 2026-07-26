<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useAuth } from '../../stores/auth'
import { apiUpdateProfile, ApiError } from '../../api/client'
import { bookableCategories } from '../../data/bookableServices'

const { state, fetchUser } = useAuth()
const saving = ref(false)
const message = ref('')
const error = ref('')
const errors = ref({})

const isProvider = computed(() => state.user?.role === 'provider')
const categories = bookableCategories.map((c) => c.title)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  category: '',
  subcategory: '',
  experience_years: '',
  service_areas: '',
  cnic: '',
  qualifications: '',
})

function hydrate() {
  const u = state.user || {}
  const p = u.profile || {}
  form.name = u.name || ''
  form.email = u.email || ''
  form.phone = u.phone || ''
  form.category = p.category || categories[0] || ''
  form.subcategory = p.subcategory || ''
  form.experience_years = p.experience_years ?? ''
  form.service_areas = p.service_areas || ''
  form.cnic = p.cnic || ''
  form.qualifications = p.qualifications || ''
}

hydrate()
watch(() => state.user, hydrate, { deep: true })

async function save() {
  saving.value = true
  message.value = ''
  error.value = ''
  errors.value = {}
  try {
    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone,
    }
    if (isProvider.value) {
      Object.assign(payload, {
        category: form.category,
        subcategory: form.subcategory,
        experience_years: form.experience_years === '' ? null : Number(form.experience_years),
        service_areas: form.service_areas,
        cnic: form.cnic,
        qualifications: form.qualifications,
      })
    }
    const res = await apiUpdateProfile(payload)
    state.user = res.user
    await fetchUser()
    message.value = res.message || 'Profile updated.'
  } catch (err) {
    if (err instanceof ApiError) {
      error.value = err.message
      errors.value = err.errors || {}
    } else {
      error.value = 'Failed to update profile'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="db-panel">
    <div class="db-panel-head">
      <div>
        <h2>Profile settings</h2>
        <p>Update your account details</p>
      </div>
    </div>
    <div class="db-panel-body">
      <p v-if="message" class="bk-success">{{ message }}</p>
      <p v-if="error" class="auth-alert">{{ error }}</p>

      <form class="bk-form" @submit.prevent="save">
        <div class="bk-section">
          <h3>Account</h3>
          <div class="bk-grid">
            <label>Full name<input v-model="form.name" required /><small v-if="errors.name">{{ errors.name[0] }}</small></label>
            <label>Email<input v-model="form.email" type="email" required /><small v-if="errors.email">{{ errors.email[0] }}</small></label>
            <label>Phone<input v-model="form.phone" type="tel" :required="isProvider" /><small v-if="errors.phone">{{ errors.phone[0] }}</small></label>
          </div>
        </div>

        <div v-if="isProvider" class="bk-section">
          <h3>Professional profile</h3>
          <div class="bk-grid">
            <label>
              Category
              <select v-model="form.category">
                <option v-for="c in categories" :key="c">{{ c }}</option>
              </select>
            </label>
            <label>Subcategory<input v-model="form.subcategory" /></label>
            <label>Experience (years)<input v-model="form.experience_years" type="number" min="0" max="60" /></label>
            <label>Service areas<input v-model="form.service_areas" /></label>
            <label>CNIC<input v-model="form.cnic" /></label>
            <label class="full">Qualifications<textarea v-model="form.qualifications" rows="3" /></label>
          </div>
        </div>

        <div class="bk-actions">
          <button type="submit" class="db-btn db-btn-gold" :disabled="saving">
            {{ saving ? 'Saving…' : 'Save profile' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
