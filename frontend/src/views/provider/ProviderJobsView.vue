<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  apiProviderJobs,
  apiStartBooking,
  apiCompleteBooking,
  apiCancelBooking,
  ApiError,
} from '../../api/client'
import { BOOKING_STATUSES, statusClass, statusLabel } from '../../utils/booking'

const route = useRoute()
const jobs = ref([])
const loading = ref(true)
const error = ref('')
const actionError = ref('')
const actingId = ref(null)
const filterStatus = ref('All')

const statusOptions = computed(() => ['All', ...BOOKING_STATUSES.filter((s) => s !== 'received')])
const acceptedRef = computed(() => route.query.accepted || '')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiProviderJobs(filterStatus.value === 'All' ? undefined : filterStatus.value)
    jobs.value = res.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load jobs'
    jobs.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(filterStatus, load)

async function runAction(item, action) {
  actingId.value = item.id
  actionError.value = ''
  try {
    if (action === 'start') await apiStartBooking(item.id)
    if (action === 'complete') await apiCompleteBooking(item.id)
    if (action === 'cancel') {
      if (!confirm(`Cancel job ${item.reference}?`)) return
      await apiCancelBooking(item.id)
    }
    await load()
  } catch (err) {
    actionError.value = err instanceof ApiError ? err.message : 'Action failed'
  } finally {
    actingId.value = null
  }
}
</script>

<template>
  <div>
    <div v-if="acceptedRef" class="bk-success">
      <strong>Booking accepted.</strong> Reference: {{ acceptedRef }} — manage it below.
    </div>
    <p v-if="actionError" class="auth-alert">{{ actionError }}</p>

    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>My jobs</h2>
          <p>Accepted bookings — start work, complete, or cancel if needed</p>
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
                <th>Accepted</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in jobs" :key="item.id">
                <td>{{ item.accepted_at ? new Date(item.accepted_at).toLocaleString() : '—' }}</td>
                <td>{{ item.reference }}</td>
                <td>
                  <div>{{ item.payload?.name || item.customer?.name || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.mobile || item.customer?.phone || '' }}</small>
                </td>
                <td>
                  <div>{{ item.payload?.service || '—' }}</div>
                  <small class="muted-line">{{ item.payload?.category }} · {{ item.payload?.city }}</small>
                </td>
                <td><span class="db-status" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span></td>
                <td>
                  <div class="bk-row-actions">
                    <RouterLink class="db-btn db-btn-ghost" :to="`/dashboard/provider/jobs/${item.id}`">View</RouterLink>
                    <button
                      v-if="item.status === 'assigned'"
                      type="button"
                      class="db-btn db-btn-primary"
                      :disabled="actingId === item.id"
                      @click="runAction(item, 'start')"
                    >
                      Start
                    </button>
                    <button
                      v-if="item.status === 'in_progress'"
                      type="button"
                      class="db-btn db-btn-gold"
                      :disabled="actingId === item.id"
                      @click="runAction(item, 'complete')"
                    >
                      Complete
                    </button>
                    <button
                      v-if="['assigned'].includes(item.status)"
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
