<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  apiGetBooking,
  apiAcceptBooking,
  apiProposeBudget,
  apiStartBooking,
  apiCancelBooking,
  ApiError,
} from '../../api/client'
import { bookingTimeline, formatMoney, statusClass, statusLabel } from '../../utils/booking'

const route = useRoute()
const router = useRouter()
const booking = ref(null)
const loading = ref(true)
const error = ref('')
const acting = ref(false)
const offerAmount = ref('')
const note = ref('')

const fromJobs = computed(() => route.path.includes('/jobs/'))

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiGetBooking(route.params.id)
    booking.value = res.booking
    if (res.booking?.current_offer != null) {
      offerAmount.value = String(res.booking.current_offer)
    } else if (res.booking?.customer_budget != null) {
      offerAmount.value = String(res.booking.customer_budget)
    }
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
const canNegotiate = computed(() =>
  ['assigned', 'quoted'].includes(booking.value?.status) && booking.value?.provider,
)
const canStart = computed(() => booking.value?.status === 'confirmed')
const canCancel = computed(() =>
  ['assigned', 'quoted', 'confirmed'].includes(booking.value?.status),
)
const offers = computed(() => [...(booking.value?.offers || [])].reverse())
const waitingForCustomer = computed(() =>
  booking.value?.status === 'quoted' && booking.value?.current_offer_by === 'provider',
)

async function run(action) {
  if (!booking.value) return
  acting.value = true
  error.value = ''
  try {
    let res
    if (action === 'accept') {
      const payload = { note: note.value || undefined }
      if (offerAmount.value !== '') payload.amount = Number(offerAmount.value)
      res = await apiAcceptBooking(booking.value.id, payload)
      router.push({ path: '/dashboard/provider/jobs', query: { accepted: res.booking.reference } })
      return
    }
    if (action === 'propose') {
      const amount = Number(offerAmount.value)
      if (!amount && amount !== 0) throw new Error('Enter a valid quotation amount')
      res = await apiProposeBudget(booking.value.id, amount, note.value || undefined)
    }
    if (action === 'start') {
      res = await apiStartBooking(booking.value.id, note.value || undefined)
    }
    if (action === 'cancel') {
      if (!confirm(`Cancel booking ${booking.value.reference}?`)) return
      res = await apiCancelBooking(booking.value.id, note.value || undefined)
    }
    booking.value = res.booking
    note.value = ''
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : (err.message || 'Action failed')
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
            <div class="bk-money-pills">
              <span>Customer budget: <strong>{{ formatMoney(booking.customer_budget) }}</strong></span>
              <span>Current offer: <strong>{{ formatMoney(booking.current_offer) }}</strong>
                <em v-if="booking.current_offer_by">({{ booking.current_offer_by }})</em>
              </span>
              <span>Deal: <strong>{{ formatMoney(booking.deal_amount) }}</strong></span>
            </div>
          </div>

          <div v-if="waitingForCustomer" class="bk-success">
            Quotation sent. Waiting for the customer to accept {{ formatMoney(booking.current_offer) }}.
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
              <div class="db-profile-row"><span class="k">Payment</span><span class="v">{{ booking.payload?.payment || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Description</span><span class="v">{{ booking.payload?.description || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Note</span><span class="v">{{ booking.provider_note || '—' }}</span></div>
            </div>
          </div>

          <div class="bk-offers">
            <h3>Budget & quotation trail</h3>
            <div v-if="!offers.length" class="db-empty" style="padding: 16px;">No offers yet.</div>
            <div v-else class="bk-offer-list">
              <div v-for="(o, i) in offers" :key="i" class="bk-offer">
                <strong>{{ formatMoney(o.amount) }}</strong>
                <span>{{ o.by }}{{ o.action === 'accepted' ? ' · accepted' : '' }}</span>
                <small>{{ o.note || '—' }}</small>
                <small>{{ o.at ? new Date(o.at).toLocaleString() : '' }}</small>
              </div>
            </div>
          </div>

          <div v-if="isOpen || canNegotiate || canStart || canCancel" class="bk-action-box">
            <div v-if="isOpen || canNegotiate" class="bk-grid" style="margin-bottom: 8px;">
              <label>
                Quotation amount (PKR)
                <input v-model="offerAmount" type="number" min="0" step="1" :placeholder="isOpen ? 'Optional on accept' : 'Required'" />
              </label>
              <label>
                Note
                <input v-model="note" type="text" placeholder="Optional message" />
              </label>
            </div>
            <label v-else>
              Optional note
              <textarea v-model="note" rows="2" />
            </label>
            <div class="bk-row-actions">
              <button v-if="isOpen" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('accept')">
                Accept{{ offerAmount !== '' ? ' + send quotation' : ' booking' }}
              </button>
              <button v-if="canNegotiate" type="button" class="db-btn db-btn-primary" :disabled="acting" @click="run('propose')">
                Send / update quotation
              </button>
              <button v-if="canStart" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('start')">
                Start job
              </button>
              <button v-if="canCancel" type="button" class="db-btn db-btn-ghost" :disabled="acting" @click="run('cancel')">
                Cancel
              </button>
              <button type="button" class="db-btn db-btn-ghost" @click="load">Refresh</button>
            </div>
            <p class="bk-hint">
              Only the customer can accept your quotation and mark the job completed. After they accept, you can start the job.
            </p>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
