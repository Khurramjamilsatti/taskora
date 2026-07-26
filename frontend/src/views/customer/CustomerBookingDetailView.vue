<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { apiGetBooking, apiCancelBooking, ApiError } from '../../api/client'
import { bookingTimeline, statusClass, statusLabel } from '../../utils/booking'

const route = useRoute()
const booking = ref(null)
const loading = ref(true)
const error = ref('')
const acting = ref(false)

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
const canCancel = computed(() => ['received', 'assigned'].includes(booking.value?.status))

async function cancelBooking() {
  if (!booking.value || !canCancel.value) return
  if (!confirm(`Cancel booking ${booking.value.reference}?`)) return
  acting.value = true
  error.value = ''
  try {
    const res = await apiCancelBooking(booking.value.id)
    booking.value = res.booking
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Cancel failed'
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
          <h2>Booking details</h2>
          <p v-if="booking">{{ booking.reference }}</p>
        </div>
        <RouterLink to="/dashboard/customer/bookings" class="db-btn db-btn-ghost">← Back to list</RouterLink>
      </div>
      <div class="db-panel-body">
        <div v-if="loading" class="db-empty">Loading…</div>
        <div v-else-if="error" class="db-empty">{{ error }}</div>
        <template v-else-if="booking">
          <div class="bk-detail-top">
            <span class="db-status" :class="statusClass(booking.status)">{{ statusLabel(booking.status) }}</span>
            <div class="bk-row-actions">
              <button
                v-if="canCancel"
                type="button"
                class="db-btn db-btn-ghost"
                :disabled="acting"
                @click="cancelBooking"
              >
                Cancel booking
              </button>
              <button type="button" class="db-btn db-btn-ghost" @click="load">Refresh</button>
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
              <div class="db-profile-row"><span class="k">Preferred date</span><span class="v">{{ booking.payload?.preferred_date || '—' }}</span></div>
            </div>
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Provider</span><span class="v">{{ booking.provider?.name || 'Not assigned yet' }}</span></div>
              <div class="db-profile-row"><span class="k">Provider phone</span><span class="v">{{ booking.provider?.phone || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Provider note</span><span class="v">{{ booking.provider_note || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Budget</span><span class="v">{{ booking.payload?.budget || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Payment</span><span class="v">{{ booking.payload?.payment || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Description</span><span class="v">{{ booking.payload?.description || '—' }}</span></div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
