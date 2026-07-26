<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiMyBookings, apiCancelBooking, ApiError } from '../../api/client'
import { bookableCategories } from '../../data/bookableServices'
import { BOOKING_STATUSES, formatMoney, statusClass, statusLabel } from '../../utils/booking'

const route = useRoute()
const router = useRouter()
const bookings = ref([])
const loading = ref(true)
const error = ref('')
const actionError = ref('')
const actingId = ref(null)

const filters = ref({
  q: '',
  status: 'All',
  category: 'All',
  urgency: 'All',
})

const statusOptions = computed(() => ['All', ...BOOKING_STATUSES])
const categoryOptions = computed(() => ['All', ...bookableCategories.map((c) => c.title)])
const urgencyOptions = ['All', 'Normal', 'Urgent', 'Emergency']

const justCreated = computed(() => route.query.created === '1')
const createdRef = computed(() => route.query.ref || '')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiMyBookings()
    bookings.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load bookings'
    bookings.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(() => route.query.ref, () => {
  if (route.query.created === '1') load()
})

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
      item.provider?.name,
    ].join(' ').toLowerCase()
    return hay.includes(q)
  })
})

function canCancel(item) {
  return ['received', 'assigned', 'quoted', 'confirmed'].includes(item.status)
}

async function cancelBooking(item) {
  if (!canCancel(item)) return
  if (!confirm(`Cancel booking ${item.reference}?`)) return
  actingId.value = item.id
  actionError.value = ''
  try {
    await apiCancelBooking(item.id)
    await load()
  } catch (err) {
    actionError.value = err instanceof ApiError ? err.message : 'Cancel failed'
  } finally {
    actingId.value = null
  }
}

function openDetail(item) {
  router.push(`/dashboard/customer/bookings/${item.id}`)
}
</script>

<template>
  <div>
    <div v-if="justCreated" class="bk-success">
      <strong>Booking submitted.</strong>
      <span v-if="createdRef"> Reference: {{ createdRef }}</span>
      Providers can now accept your request.
    </div>
    <p v-if="actionError" class="auth-alert">{{ actionError }}</p>

    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>My bookings</h2>
          <p>Track status from request → accepted → in progress → completed</p>
        </div>
        <RouterLink to="/dashboard/customer/services" class="db-btn db-btn-gold">Book a Service</RouterLink>
      </div>
      <div class="db-panel-body">
        <div class="bk-filters">
          <input v-model="filters.q" type="search" class="bk-input" placeholder="Search reference, service, provider…" />
          <select v-model="filters.status" class="bk-input">
            <option v-for="s in statusOptions" :key="s" :value="s">Status: {{ s === 'All' ? 'All' : statusLabel(s) }}</option>
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
                <th>Service</th>
                <th>Provider</th>
                <th>Budget / Deal</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filtered" :key="item.id">
                <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                <td>{{ item.reference }}</td>
                <td>
                  <div>{{ item.payload?.service || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.category }} · {{ item.payload?.city }}</small>
                </td>
                <td>{{ item.provider?.name || 'Waiting for provider' }}</td>
                <td>
                  <div>{{ formatMoney(item.deal_amount || item.current_offer || item.customer_budget) }}</div>
                  <small class="muted-line" v-if="item.current_offer_by && !item.deal_amount">
                    {{ item.current_offer_by }} offer
                  </small>
                </td>
                <td>
                  <span class="db-status" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
                </td>
                <td>
                  <div class="bk-row-actions">
                    <button type="button" class="db-btn db-btn-ghost" @click="openDetail(item)">View</button>
                    <button
                      v-if="canCancel(item)"
                      type="button"
                      class="db-btn db-btn-ghost"
                      :disabled="actingId === item.id"
                      @click="cancelBooking(item)"
                    >
                      Cancel
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
