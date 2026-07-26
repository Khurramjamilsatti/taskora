<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuth } from '../../stores/auth'
import { apiBookingRequests, apiProviderJobs } from '../../api/client'

const { state } = useAuth()
const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => (profile.value.status || 'pending_verification').replaceAll('_', ' '))
const openCount = ref(0)
const jobCount = ref(0)
const activeCount = ref(0)

onMounted(async () => {
  try {
    const [requests, jobs] = await Promise.all([
      apiBookingRequests(),
      apiProviderJobs(),
    ])
    openCount.value = (requests.data || []).length
    const list = jobs.data || []
    jobCount.value = list.length
    activeCount.value = list.filter((j) => ['assigned', 'in_progress'].includes(j.status)).length
  } catch {
    // keep zeros
  }
})
</script>

<template>
  <div>
    <div class="db-hero-strip">
      <div>
        <div class="eyebrow">Provider workspace</div>
        <h2>{{ state.user?.name }}</h2>
        <p>
          {{ profile.category || 'Professional' }}
          <span v-if="profile.subcategory"> · {{ profile.subcategory }}</span>
        </p>
      </div>
      <div class="hero-pills">
        <RouterLink class="db-btn db-btn-gold" to="/dashboard/provider/requests">Booking Requests</RouterLink>
        <RouterLink class="db-btn db-btn-ghost" to="/dashboard/provider/jobs" style="border-color: rgba(255,255,255,0.35); color: #fff;">
          My Jobs
        </RouterLink>
      </div>
    </div>

    <div class="db-stats">
      <div class="db-stat">
        <div class="label">Open requests</div>
        <div class="value accent">{{ openCount }}</div>
        <div class="hint">Available to accept</div>
      </div>
      <div class="db-stat">
        <div class="label">Active jobs</div>
        <div class="value gold">{{ activeCount }}</div>
        <div class="hint">Assigned or in progress</div>
      </div>
      <div class="db-stat">
        <div class="label">All my jobs</div>
        <div class="value">{{ jobCount }}</div>
        <div class="hint">Including completed</div>
      </div>
      <div class="db-stat">
        <div class="label">Verification</div>
        <div class="value" style="font-size: 15px; text-transform: capitalize;">{{ statusLabel }}</div>
        <div class="hint">Provider account status</div>
      </div>
    </div>

    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Booking workflow</h2>
          <p>How jobs move from request to completion</p>
        </div>
      </div>
      <div class="db-panel-body">
        <div class="db-flow wrap">
          <div class="db-flow-step"><div class="bubble">1</div><div class="txt">Accept request</div><div class="arrow">→</div></div>
          <div class="db-flow-step"><div class="bubble">2</div><div class="txt">Start job</div><div class="arrow">→</div></div>
          <div class="db-flow-step"><div class="bubble">3</div><div class="txt">Complete</div></div>
        </div>
        <div class="db-action-grid" style="margin-top: 18px;">
          <RouterLink class="db-action" to="/dashboard/provider/requests">
            <strong>Review booking requests</strong>
            <span>Claim open customer service needs</span>
          </RouterLink>
          <RouterLink class="db-action" to="/dashboard/provider/jobs">
            <strong>Manage my jobs</strong>
            <span>Start and complete assigned bookings</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
