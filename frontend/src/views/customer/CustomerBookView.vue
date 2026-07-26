<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../stores/auth'
import { apiSubmitForm, ApiError } from '../../api/client'
import { bookableCategories } from '../../data/bookableServices'

const route = useRoute()
const router = useRouter()
const { state } = useAuth()

const cities = ['Islamabad', 'Rawalpindi', 'Lahore', 'Karachi', 'Faisalabad', 'Peshawar', 'Multan']
const categories = bookableCategories.map((c) => c.title)

const form = reactive({
  cnic: '',
  name: state.user?.name || '',
  mobile: state.user?.phone || '',
  email: state.user?.email || '',
  city: cities[0],
  address: '',
  map_link: '',
  category: categories[0],
  service: '',
  description: '',
  urgency: 'Normal',
  preferred_date: '',
  budget: '',
  quotation: 'Yes',
  inspection: 'No',
  payment: 'Cash',
  signature: '',
})

const error = ref('')
const loading = ref(false)

const serviceOptions = computed(() => {
  const cat = bookableCategories.find((c) => c.title === form.category)
  return cat?.services || []
})

function applyQuery() {
  if (route.query.category && categories.includes(String(route.query.category))) {
    form.category = String(route.query.category)
  }
  if (route.query.service) {
    form.service = String(route.query.service)
  } else if (serviceOptions.value.length && !form.service) {
    form.service = serviceOptions.value[0]
  }
}

onMounted(applyQuery)
watch(() => route.query, applyQuery, { deep: true })
watch(
  () => form.category,
  () => {
    if (!serviceOptions.value.includes(form.service)) {
      form.service = serviceOptions.value[0] || ''
    }
  },
)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('booking', { ...form })
    router.push({
      path: '/dashboard/customer/bookings',
      query: { ref: res.reference, created: '1' },
    })
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Booking failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Book a service</h2>
          <p>Confirm details and submit your booking request</p>
        </div>
        <RouterLink to="/dashboard/customer/services" class="db-btn db-btn-ghost">← All services</RouterLink>
      </div>
      <div class="db-panel-body">
        <p v-if="error" class="auth-alert">{{ error }}</p>

        <form class="bk-form" @submit.prevent="submit">
          <div class="bk-section">
            <h3>Customer information</h3>
            <div class="bk-grid">
              <label>Full name<input v-model="form.name" required /></label>
              <label>Mobile<input v-model="form.mobile" type="tel" required /></label>
              <label>Email<input v-model="form.email" type="email" /></label>
              <label>
                City
                <select v-model="form.city">
                  <option v-for="c in cities" :key="c">{{ c }}</option>
                </select>
              </label>
              <label class="full">Address<input v-model="form.address" required /></label>
              <label class="full">Map link<input v-model="form.map_link" placeholder="Optional Google Maps link" /></label>
            </div>
          </div>

          <div class="bk-section">
            <h3>Service details</h3>
            <div class="bk-grid">
              <label>
                Category
                <select v-model="form.category">
                  <option v-for="c in categories" :key="c">{{ c }}</option>
                </select>
              </label>
              <label>
                Service
                <select v-model="form.service" required>
                  <option v-for="s in serviceOptions" :key="s" :value="s">{{ s }}</option>
                </select>
              </label>
              <label class="full">Description<textarea v-model="form.description" rows="3" /></label>
              <label>
                Urgency
                <select v-model="form.urgency">
                  <option>Normal</option>
                  <option>Urgent</option>
                  <option>Emergency</option>
                </select>
              </label>
              <label>Preferred date<input v-model="form.preferred_date" type="date" /></label>
              <label>Budget (PKR)<input v-model="form.budget" type="number" min="0" /></label>
              <label>
                Payment
                <select v-model="form.payment">
                  <option>Cash</option>
                  <option>Bank Transfer</option>
                  <option>Card</option>
                  <option>Wallet</option>
                  <option>Easypaisa</option>
                  <option>JazzCash</option>
                </select>
              </label>
            </div>
          </div>

          <div class="bk-actions">
            <button type="submit" class="db-btn db-btn-gold" :disabled="loading">
              {{ loading ? 'Submitting…' : 'Confirm booking' }}
            </button>
            <RouterLink to="/dashboard/customer/bookings" class="db-btn db-btn-ghost">Cancel</RouterLink>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
