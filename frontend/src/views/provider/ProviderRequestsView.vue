<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiBookingRequests, apiAcceptBooking, ApiError } from '../../api/client'
import { statusClass, statusLabel } from '../../utils/booking'
import { confirmAction, toastError, toastSuccess } from '../../composables/useFeedback'

const router = useRouter()
const requests = ref([])
const loading = ref(true)
const error = ref('')
const actingId = ref(null)
const filters = ref({
  q: '',
  category: 'All',
  urgency: 'All',
})

const categoryOptions = computed(() => {
  const set = new Set()
  requests.value.forEach((item) => {
    if (item.payload?.category) set.add(item.payload.category)
  })
  return ['All', ...[...set].sort()]
})

const urgencyOptions = ['All', 'Normal', 'Urgent', 'Emergency']

async function load() {
  loading.value = true
  error.value = ''
  try {
    const params = {}
    if (filters.value.q.trim()) params.q = filters.value.q.trim()
    if (filters.value.category !== 'All') params.category = filters.value.category
    const res = await apiBookingRequests(params)
    requests.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load booking requests'
    requests.value = []
    toastError('Could not load requests', error.value)
  } finally {
    loading.value = false
  }
}

onMounted(load)

const filtered = computed(() =>
  requests.value.filter((item) => {
    if (filters.value.urgency !== 'All' && item.payload?.urgency !== filters.value.urgency) return false
    return true
  }),
)

async function accept(item) {
  const ok = await confirmAction({
    title: 'Accept this booking?',
    message: `${item.reference} will move to My Jobs so you can send a quotation.`,
    confirmLabel: 'Accept booking',
  })
  if (!ok) return
  actingId.value = item.id
  try {
    await apiAcceptBooking(item.id)
    toastSuccess('Booking accepted', `${item.reference} is now in My Jobs.`)
    router.push({ path: '/dashboard/provider/jobs', query: { accepted: item.reference } })
  } catch (err) {
    toastError('Accept failed', err instanceof ApiError ? err.message : 'Please try again')
    await load()
  } finally {
    actingId.value = null
  }
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Open booking requests</h2>
          <p>Accept a request → quote → customer accepts deal → you start</p>
        </div>
        <button type="button" class="db-btn db-btn-ghost" @click="load">Refresh</button>
      </div>
      <div class="db-panel-body">
        <div class="bk-filters">
          <input
            v-model="filters.q"
            type="search"
            class="bk-input"
            placeholder="Search reference, service, city…"
            @keyup.enter="load"
          />
          <select v-model="filters.category" class="bk-input" @change="load">
            <option v-for="c in categoryOptions" :key="c" :value="c">Category: {{ c }}</option>
          </select>
          <select v-model="filters.urgency" class="bk-input">
            <option v-for="u in urgencyOptions" :key="u" :value="u">Urgency: {{ u }}</option>
          </select>
          <button type="button" class="db-btn db-btn-primary" @click="load">Apply</button>
        </div>
      </div>
    </div>

    <div class="db-panel">
      <div class="db-panel-body" style="padding: 0;">
        <div v-if="loading" class="db-empty">Loading requests…</div>
        <div v-else-if="error" class="db-empty">{{ error }}</div>
        <div v-else-if="!filtered.length" class="db-empty">No open booking requests right now.</div>
        <div v-else class="db-table-wrap">
          <table class="db-dense-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Urgency</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filtered" :key="item.id">
                <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                <td>{{ item.reference }}</td>
                <td>
                  <div>{{ item.payload?.name || item.customer?.name || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.mobile || item.customer?.phone || '' }}</small>
                </td>
                <td>
                  <div>{{ item.payload?.service || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.category }} · {{ item.payload?.city }}</small>
                </td>
                <td>{{ item.payload?.urgency || '—' }}</td>
                <td><span class="db-status" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span></td>
                <td>
                  <div class="bk-row-actions">
                    <RouterLink class="db-btn db-btn-ghost" :to="`/dashboard/provider/requests/${item.id}`">View</RouterLink>
                    <button
                      type="button"
                      class="db-btn db-btn-gold"
                      :disabled="actingId === item.id"
                      @click="accept(item)"
                    >
                      {{ actingId === item.id ? 'Accepting…' : 'Accept' }}
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
