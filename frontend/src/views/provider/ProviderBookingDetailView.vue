<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  apiGetBooking,
  apiAcceptBooking,
  apiStartBooking,
  apiCompleteBooking,
  apiCancelBooking,
  ApiError,
} from '../../api/client'
import { bookingTimeline, statusClass, statusLabel } from '../../utils/booking'

const route = useRoute()
const router = useRouter()
const booking = ref(null)
const loading = ref(true)
const error = ref('')
const acting = ref(false)
const note = ref('')

const fromJobs = computed(() => route.path.includes('/jobs/'))

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiGetBooking(route.params.id)
    booking.value = res.booking
  } catch (err) {
    error.value = err.message || 'Failed to load booking'
    booking.value = null
  } finally {
    loading.value = false
  }
}

onMounted(load)

const timeline = computed(() => (booking.value ? bookingTimeline(booking.value) : []))
const isOpen = computed(() => booking.value?.status === 'received' && !booking.value?.provider)
const isMineAssigned = computed(() => booking.value?.status === 'assigned')
const isMineProgress = computed(() => booking.value?.status === 'in_progress')
const canCancel = computed(() => ['assigned'].includes(booking.value?.status))

async function run(action) {
  if (!booking.value) return
  acting.value = true
  error.value = ''
  try {
    let res
    if (action === 'accept') {
      res = await apiAcceptBooking(booking.value.id, note.value || undefined)
      router.push({ path: '/dashboard/provider/jobs', query: { accepted: res.booking.reference } })
      return
    }
    if (action === 'start') res = await apiStartBooking(booking.value.id, note.value || undefined)
    if (action === 'complete') res = await apiCompleteBooking(booking.value.id, note.value || undefined)
    if (action === 'cancel') {
      if (!confirm(`Cancel booking ${booking.value.reference}?`)) return
      res = await apiCancelBooking(booking.value.id, note.value || undefined)
    }
    booking.value = res.booking
    note.value = ''
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Action failed'
  } finally {
    acting.value = false
  }
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>{{ fromJobs ? 'Job details' : 'Request details' }}</h2>
          <p v-if="booking">{{ booking.reference }}</p>
        </div>
        <RouterLink
          :to="fromJobs ? '/dashboard/provider/jobs' : '/dashboard/provider/requests'"
          class="db-btn db-btn-ghost"
        >
          ← Back
        </RouterLink>
      </div>
      <div class="db-panel-body">
        <div v-if="loading" class="db-empty">Loading…</div>
        <div v-else-if="error && !booking" class="db-empty">{{ error }}</div>
        <template v-else-if="booking">
          <p v-if="error" class="auth-alert">{{ error }}</p>
          <div class="bk-detail-top">
            <span class="db-status" :class="statusClass(booking.status)">{{ statusLabel(booking.status) }}</span>
          </div>

          <div class="bk-timeline">
            <div
              v-for="step in timeline"
              :key="step.key"
              class="bk-step"
              :class="{ done: step.done, current: booking.status === step.key }"
            >
              <div class="dot" />
              <div>
                <strong>{{ step.label }}</strong>
                <small>{{ step.at ? new Date(step.at).toLocaleString() : 'Pending' }}</small>
              </div>
            </div>
          </div>

          <div class="db-grid-2" style="margin-top: 18px;">
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Customer</span><span class="v">{{ booking.payload?.name || booking.customer?.name }}</span></div>
              <div class="db-profile-row"><span class="k">Mobile</span><span class="v">{{ booking.payload?.mobile || booking.customer?.phone || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Service</span><span class="v">{{ booking.payload?.service }}</span></div>
              <div class="db-profile-row"><span class="k">Category</span><span class="v">{{ booking.payload?.category }}</span></div>
              <div class="db-profile-row"><span class="k">City</span><span class="v">{{ booking.payload?.city }}</span></div>
              <div class="db-profile-row"><span class="k">Address</span><span class="v">{{ booking.payload?.address }}</span></div>
            </div>
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Urgency</span><span class="v">{{ booking.payload?.urgency || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Preferred date</span><span class="v">{{ booking.payload?.preferred_date || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Budget</span><span class="v">{{ booking.payload?.budget || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Payment</span><span class="v">{{ booking.payload?.payment || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Description</span><span class="v">{{ booking.payload?.description || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Note</span><span class="v">{{ booking.provider_note || '—' }}</span></div>
            </div>
          </div>

          <div v-if="isOpen || isMineAssigned || isMineProgress || canCancel" class="bk-action-box">
            <label>
              Optional note
              <textarea v-model="note" rows="2" placeholder="Message for the customer / job log" />
            </label>
            <div class="bk-row-actions">
              <button v-if="isOpen" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('accept')">
                Accept booking
              </button>
              <button v-if="isMineAssigned" type="button" class="db-btn db-btn-primary" :disabled="acting" @click="run('start')">
                Start job
              </button>
              <button v-if="isMineProgress" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('complete')">
                Mark completed
              </button>
              <button v-if="canCancel" type="button" class="db-btn db-btn-ghost" :disabled="acting" @click="run('cancel')">
                Cancel job
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
