<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
  apiGetBooking,
  apiCancelBooking,
  apiProposeBudget,
  apiAcceptQuote,
  apiCompleteBooking,
  ApiError,
} from '../../api/client'
import {
  bookingNextStep,
  bookingTimeline,
  formatMoney,
  statusClass,
  statusLabel,
} from '../../utils/booking'
import { confirmAction, toastError, toastSuccess } from '../../composables/useFeedback'
import BookingChat from '../../components/BookingChat.vue'
import BookingFeedback from '../../components/BookingFeedback.vue'

const route = useRoute()
const booking = ref(null)
const loading = ref(true)
const error = ref('')
const acting = ref(false)
const offerAmount = ref('')
const note = ref('')
let pollTimer = null

async function load({ silent = false } = {}) {
  if (!silent) {
    loading.value = true
    error.value = ''
  }
  try {
    const res = await apiGetBooking(route.params.id)
    booking.value = res.booking
    if (!silent) {
      if (res.booking?.current_offer != null) {
        offerAmount.value = String(res.booking.current_offer)
      } else if (res.booking?.customer_budget != null) {
        offerAmount.value = String(res.booking.customer_budget)
      }
    }
  } catch (err) {
    if (!silent) {
      error.value = err.message || 'Failed to load booking'
      booking.value = null
      toastError('Could not load booking', error.value)
    }
  } finally {
    if (!silent) loading.value = false
  }
}

onMounted(() => {
  load()
  pollTimer = setInterval(() => load({ silent: true }), 12000)
})
onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer)
})

const timeline = computed(() => (booking.value ? bookingTimeline(booking.value) : []))
const nextStep = computed(() => bookingNextStep(booking.value, 'customer'))
const canCancel = computed(() =>
  ['received', 'assigned', 'quoted', 'confirmed'].includes(booking.value?.status),
)
const canNegotiate = computed(() => ['assigned', 'quoted'].includes(booking.value?.status))
const canAcceptQuote = computed(() =>
  booking.value?.status === 'quoted' && booking.value?.current_offer_by === 'provider',
)
const canComplete = computed(() => booking.value?.status === 'in_progress')
const canChat = computed(() =>
  ['confirmed', 'in_progress', 'completed'].includes(booking.value?.status),
)
const offers = computed(() => [...(booking.value?.offers || [])].reverse())

async function run(action) {
  if (!booking.value) return

  if (action === 'accept-quote') {
    const ok = await confirmAction({
      title: 'Accept this quotation?',
      message: `Lock the deal at ${formatMoney(booking.value.current_offer)}. The provider can then start the job.`,
      confirmLabel: 'Accept deal',
    })
    if (!ok) return
  }
  if (action === 'complete') {
    const ok = await confirmAction({
      title: 'Mark job completed?',
      message: 'Confirm only when the work is finished to your satisfaction. This closes the booking.',
      confirmLabel: 'Yes, completed',
    })
    if (!ok) return
  }
  if (action === 'cancel') {
    const ok = await confirmAction({
      title: 'Cancel this booking?',
      message: `Reference ${booking.value.reference} will be cancelled for both sides.`,
      confirmLabel: 'Cancel booking',
      danger: true,
    })
    if (!ok) return
  }

  acting.value = true
  error.value = ''
  try {
    let res
    if (action === 'propose') {
      const amount = Number(offerAmount.value)
      if (!amount && amount !== 0) throw new Error('Enter a valid budget amount')
      res = await apiProposeBudget(booking.value.id, amount, note.value || undefined)
      toastSuccess('Budget sent', `Your offer ${formatMoney(amount)} is with the provider.`)
    }
    if (action === 'accept-quote') {
      res = await apiAcceptQuote(booking.value.id, note.value || undefined)
      toastSuccess('Deal locked', 'Chat is open. The provider can start the job.')
    }
    if (action === 'complete') {
      res = await apiCompleteBooking(booking.value.id, note.value || undefined)
      toastSuccess('Job completed', 'Please leave feedback for your provider below.')
    }
    if (action === 'cancel') {
      res = await apiCancelBooking(booking.value.id, note.value || undefined)
      toastSuccess('Booking cancelled', booking.value.reference)
    }
    booking.value = res.booking
    note.value = ''
  } catch (err) {
    const msg = err instanceof ApiError ? err.message : (err.message || 'Action failed')
    error.value = msg
    toastError('Action failed', msg)
  } finally {
    acting.value = false
  }
}

function onFeedbackSubmitted(updated) {
  booking.value = updated
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Booking details</h2>
          <p v-if="booking">{{ booking.reference }} · {{ statusLabel(booking.status) }}</p>
        </div>
        <RouterLink to="/dashboard/customer/bookings" class="db-btn db-btn-ghost">← Back to list</RouterLink>
      </div>
      <div class="db-panel-body">
        <div v-if="loading" class="db-empty">Loading…</div>
        <div v-else-if="error && !booking" class="db-empty">{{ error }}</div>
        <template v-else-if="booking">
          <div class="bk-detail-top">
            <span class="db-status" :class="statusClass(booking.status)">{{ statusLabel(booking.status) }}</span>
            <div class="bk-money-pills">
              <span>Your budget: <strong>{{ formatMoney(booking.customer_budget) }}</strong></span>
              <span>Current offer: <strong>{{ formatMoney(booking.current_offer) }}</strong>
                <em v-if="booking.current_offer_by">({{ booking.current_offer_by }})</em>
              </span>
              <span>Deal: <strong>{{ formatMoney(booking.deal_amount) }}</strong></span>
            </div>
          </div>

          <div v-if="nextStep" class="bk-next" :class="nextStep.tone">
            <div class="bk-next-ico">→</div>
            <div>
              <strong>{{ nextStep.title }}</strong>
              <span>{{ nextStep.detail }}</span>
            </div>
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
              <div class="db-profile-row"><span class="k">Service</span><span class="v">{{ booking.payload?.service }}</span></div>
              <div class="db-profile-row"><span class="k">Category</span><span class="v">{{ booking.payload?.category }}</span></div>
              <div class="db-profile-row"><span class="k">City</span><span class="v">{{ booking.payload?.city }}</span></div>
              <div class="db-profile-row"><span class="k">Address</span><span class="v">{{ booking.payload?.address }}</span></div>
              <div class="db-profile-row"><span class="k">Urgency</span><span class="v">{{ booking.payload?.urgency || '—' }}</span></div>
            </div>
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Provider</span><span class="v">{{ booking.provider?.name || 'Not assigned yet' }}</span></div>
              <div class="db-profile-row"><span class="k">Provider phone</span><span class="v">{{ booking.provider?.phone || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Provider note</span><span class="v">{{ booking.provider_note || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Payment</span><span class="v">{{ booking.payload?.payment || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Description</span><span class="v">{{ booking.payload?.description || '—' }}</span></div>
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

          <BookingChat
            v-if="booking"
            :booking-id="booking.id"
            :enabled="canChat"
          />

          <BookingFeedback
            v-if="booking.status === 'completed'"
            :booking="booking"
            @submitted="onFeedbackSubmitted"
          />

          <div v-if="canNegotiate || canAcceptQuote || canComplete || canCancel" class="bk-action-box">
            <div v-if="canNegotiate" class="bk-grid" style="margin-bottom: 8px;">
              <label>
                Your budget / counter (PKR)
                <input v-model="offerAmount" type="number" min="0" step="1" />
              </label>
              <label>
                Note
                <input v-model="note" type="text" placeholder="Optional message" />
              </label>
            </div>
            <label v-else-if="canAcceptQuote || canComplete || canCancel">
              Optional note
              <textarea v-model="note" rows="2" />
            </label>
            <div class="bk-row-actions">
              <button v-if="canNegotiate" type="button" class="db-btn db-btn-primary" :disabled="acting" @click="run('propose')">
                {{ acting ? 'Sending…' : 'Send counter / budget' }}
              </button>
              <button v-if="canAcceptQuote" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('accept-quote')">
                Accept quotation {{ formatMoney(booking.current_offer) }}
              </button>
              <button v-if="canComplete" type="button" class="db-btn db-btn-gold" :disabled="acting" @click="run('complete')">
                Mark job completed
              </button>
              <button v-if="canCancel" type="button" class="db-btn db-btn-ghost" :disabled="acting" @click="run('cancel')">
                Cancel booking
              </button>
              <button type="button" class="db-btn db-btn-ghost" @click="load()">Refresh</button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
