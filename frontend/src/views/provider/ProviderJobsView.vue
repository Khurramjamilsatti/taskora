<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  apiProviderJobs,
  apiStartBooking,
  apiCancelBooking,
  ApiError,
} from '../../api/client'
import { BOOKING_STATUSES, formatMoney, statusClass, statusLabel } from '../../utils/booking'
import { confirmAction, toastError, toastSuccess } from '../../composables/useFeedback'

const route = useRoute()
const router = useRouter()
const jobs = ref([])
const loading = ref(true)
const error = ref('')
const actingId = ref(null)
const filterStatus = ref('All')

const statusOptions = computed(() => ['All', ...BOOKING_STATUSES.filter((s) => s !== 'received')])

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiProviderJobs(filterStatus.value === 'All' ? undefined : filterStatus.value)
    jobs.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load jobs'
    jobs.value = []
    toastError('Could not load jobs', error.value)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()
  if (route.query.accepted) {
    toastSuccess('Booking accepted', `Reference ${route.query.accepted} — send a quotation next.`)
    router.replace({ path: route.path, query: {} })
  }
})
watch(filterStatus, load)

function nextHint(item) {
  if (item.status === 'assigned') return 'Send quotation'
  if (item.status === 'quoted' && item.current_offer_by === 'customer') return 'Reply to counter'
  if (item.status === 'quoted') return 'Wait for customer'
  if (item.status === 'confirmed') return 'Start job'
  if (item.status === 'in_progress') return 'Customer completes'
  return ''
}

async function runAction(item, action) {
  if (action === 'start') {
    const ok = await confirmAction({
      title: 'Start this job?',
      message: `Customer will be notified for ${item.reference}.`,
      confirmLabel: 'Start job',
    })
    if (!ok) return
  }
  if (action === 'cancel') {
    const ok = await confirmAction({
      title: 'Cancel this job?',
      message: `Reference ${item.reference} will be cancelled.`,
      confirmLabel: 'Cancel job',
      danger: true,
    })
    if (!ok) return
  }

  actingId.value = item.id
  try {
    if (action === 'start') {
      await apiStartBooking(item.id)
      toastSuccess('Job started', item.reference)
    }
    if (action === 'cancel') {
      await apiCancelBooking(item.id)
      toastSuccess('Job cancelled', item.reference)
    }
    await load()
  } catch (err) {
    toastError('Action failed', err instanceof ApiError ? err.message : 'Please try again')
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
          <h2>My jobs</h2>
          <p>Quote → customer accepts → you start → customer marks completed</p>
        </div>
        <div class="bk-row-actions">
          <select v-model="filterStatus" class="bk-input" style="width: auto;">
            <option v-for="s in statusOptions" :key="s" :value="s">
              {{ s === 'All' ? 'All statuses' : statusLabel(s) }}
            </option>
          </select>
          <button type="button" class="db-btn db-btn-ghost" @click="load">Refresh</button>
          <RouterLink to="/dashboard/provider/requests" class="db-btn db-btn-gold">Find requests</RouterLink>
        </div>
      </div>
      <div class="db-panel-body" style="padding: 0;">
        <div v-if="loading" class="db-empty">Loading jobs…</div>
        <div v-else-if="error" class="db-empty">{{ error }}</div>
        <div v-else-if="!jobs.length" class="db-empty">
          No jobs assigned yet.
          <div style="margin-top: 12px;">
            <RouterLink to="/dashboard/provider/requests" class="db-btn db-btn-primary">Browse booking requests</RouterLink>
          </div>
        </div>
        <div v-else class="db-table-wrap">
          <table class="db-dense-table">
            <thead>
              <tr>
                <th>Updated</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Service / Deal</th>
                <th>Status</th>
                <th>Next</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in jobs" :key="item.id">
                <td>{{ new Date(item.updated_at || item.created_at).toLocaleString() }}</td>
                <td>{{ item.reference }}</td>
                <td>
                  <div>{{ item.payload?.name || item.customer?.name || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.mobile || item.customer?.phone || '' }}</small>
                </td>
                <td>
                  <div>{{ item.payload?.service || '—' }}</div>
                  <small class="muted-line">
                    Offer {{ formatMoney(item.current_offer) }}
                    · Deal {{ formatMoney(item.deal_amount) }}
                  </small>
                </td>
                <td><span class="db-status" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span></td>
                <td><small class="muted-line">{{ nextHint(item) || '—' }}</small></td>
                <td>
                  <div class="bk-row-actions">
                    <RouterLink class="db-btn db-btn-ghost" :to="`/dashboard/provider/jobs/${item.id}`">View</RouterLink>
                    <button
                      v-if="item.status === 'confirmed'"
                      type="button"
                      class="db-btn db-btn-primary"
                      :disabled="actingId === item.id"
                      @click="runAction(item, 'start')"
                    >
                      Start
                    </button>
                    <button
                      v-if="['assigned', 'quoted', 'confirmed'].includes(item.status)"
                      type="button"
                      class="db-btn db-btn-ghost"
                      :disabled="actingId === item.id"
                      @click="runAction(item, 'cancel')"
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
