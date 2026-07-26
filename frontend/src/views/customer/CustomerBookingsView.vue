<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { apiMyForms } from '../../api/client'
import { bookableCategories } from '../../data/bookableServices'

const route = useRoute()
const bookings = ref([])
const loading = ref(true)
const error = ref('')

const filters = ref({
  q: '',
  status: 'All',
  category: 'All',
  urgency: 'All',
})

const statusOptions = ['All', 'received', 'pending', 'assigned', 'in_progress', 'completed', 'cancelled']
const categoryOptions = computed(() => ['All', ...bookableCategories.map((c) => c.title)])
const urgencyOptions = ['All', 'Normal', 'Urgent', 'Emergency']

const justCreated = computed(() => route.query.created === '1')
const createdRef = computed(() => route.query.ref || '')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiMyForms('booking')
    bookings.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load bookings'
    bookings.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)

const filtered = computed(() => {
  const q = filters.value.q.trim().toLowerCase()
  return bookings.value.filter((item) => {
    const p = item.payload || {}
    if (filters.value.status !== 'All' && item.status !== filters.value.status) return false
    if (filters.value.category !== 'All' && p.category !== filters.value.category) return false
    if (filters.value.urgency !== 'All' && p.urgency !== filters.value.urgency) return false
    if (!q) return true
    const hay = [
      item.reference,
      item.status,
      p.category,
      p.service,
      p.city,
      p.name,
    ].join(' ').toLowerCase()
    return hay.includes(q)
  })
})

function statusClass(status) {
  const s = String(status || '').toLowerCase()
  if (['pending', 'received', 'assigned', 'in_progress'].includes(s)) return 'warn'
  return ''
}

watch(() => route.query.ref, () => {
  if (route.query.created === '1') load()
})
</script>

<template>
  <div>
    <div v-if="justCreated" class="bk-success">
      <strong>Booking submitted.</strong>
      <span v-if="createdRef"> Reference: {{ createdRef }}</span>
    </div>

    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>My bookings</h2>
          <p>Filter and track every service request</p>
        </div>
        <RouterLink to="/dashboard/customer/services" class="db-btn db-btn-gold">Book a Service</RouterLink>
      </div>
      <div class="db-panel-body">
        <div class="bk-filters">
          <input v-model="filters.q" type="search" class="bk-input" placeholder="Search reference, service, city…" />
          <select v-model="filters.status" class="bk-input">
            <option v-for="s in statusOptions" :key="s" :value="s">Status: {{ s }}</option>
          </select>
          <select v-model="filters.category" class="bk-input">
            <option v-for="c in categoryOptions" :key="c" :value="c">Category: {{ c }}</option>
          </select>
          <select v-model="filters.urgency" class="bk-input">
            <option v-for="u in urgencyOptions" :key="u" :value="u">Urgency: {{ u }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="db-panel">
      <div class="db-panel-body" style="padding: 0;">
        <div v-if="loading" class="db-empty">Loading bookings…</div>
        <div v-else-if="error" class="db-empty">{{ error }}</div>
        <div v-else-if="!filtered.length" class="db-empty">
          No bookings found.
          <div style="margin-top: 12px;">
            <RouterLink to="/dashboard/customer/services" class="db-btn db-btn-primary">Book a Service</RouterLink>
          </div>
        </div>
        <div v-else class="db-table-wrap">
          <table class="db-dense-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Category</th>
                <th>Service</th>
                <th>City</th>
                <th>Urgency</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filtered" :key="item.id">
                <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                <td>{{ item.reference }}</td>
                <td>{{ item.payload?.category || '—' }}</td>
                <td>{{ item.payload?.service || '—' }}</td>
                <td>{{ item.payload?.city || '—' }}</td>
                <td>{{ item.payload?.urgency || '—' }}</td>
                <td>
                  <span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
