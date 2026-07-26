<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { apiBookingRequests } from '../../api/client'

const requests = ref([])
const loading = ref(true)
const error = ref('')
const filters = ref({
  q: '',
  status: 'All',
  category: 'All',
  urgency: 'All',
})

const statusOptions = ['All', 'received', 'pending', 'assigned', 'in_progress', 'completed', 'cancelled']
const urgencyOptions = ['All', 'Normal', 'Urgent', 'Emergency']

const categoryOptions = computed(() => {
  const set = new Set()
  requests.value.forEach((item) => {
    if (item.payload?.category) set.add(item.payload.category)
  })
  return ['All', ...[...set].sort()]
})

async function load() {
  loading.value = true
  error.value = ''
  try {
    const params = {}
    if (filters.value.status !== 'All') params.status = filters.value.status
    if (filters.value.q.trim()) params.q = filters.value.q.trim()
    const res = await apiBookingRequests(params)
    requests.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load booking requests'
    requests.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(() => filters.value.status, load)

const filtered = computed(() => {
  return requests.value.filter((item) => {
    const p = item.payload || {}
    if (filters.value.category !== 'All' && p.category !== filters.value.category) return false
    if (filters.value.urgency !== 'All' && p.urgency !== filters.value.urgency) return false
    return true
  })
})

function statusClass(status) {
  const s = String(status || '').toLowerCase()
  if (['pending', 'received', 'assigned', 'in_progress'].includes(s)) return 'warn'
  return ''
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Booking requests</h2>
          <p>Customer service requests across the marketplace</p>
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
          <select v-model="filters.status" class="bk-input">
            <option v-for="s in statusOptions" :key="s" :value="s">Status: {{ s }}</option>
          </select>
          <select v-model="filters.category" class="bk-input">
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
        <div v-else-if="!filtered.length" class="db-empty">No booking requests match your filters.</div>
        <div v-else class="db-table-wrap">
          <table class="db-dense-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Customer</th>
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
                <td>{{ item.payload?.name || '—' }}</td>
                <td>{{ item.payload?.category || '—' }}</td>
                <td>{{ item.payload?.service || '—' }}</td>
                <td>{{ item.payload?.city || '—' }}</td>
                <td>{{ item.payload?.urgency || '—' }}</td>
                <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
