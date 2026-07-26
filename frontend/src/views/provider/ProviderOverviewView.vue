<script setup>
import { computed } from 'vue'
import { useAuth } from '../../stores/auth'

const { state } = useAuth()
const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => (profile.value.status || 'pending_verification').replaceAll('_', ' '))
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
      </div>
    </div>

    <div class="db-stats">
      <div class="db-stat">
        <div class="label">Verification</div>
        <div class="value gold" style="font-size: 16px; text-transform: capitalize;">{{ statusLabel }}</div>
        <div class="hint">Complete verification to receive more jobs</div>
      </div>
      <div class="db-stat">
        <div class="label">Requests</div>
        <div class="value accent">Inbox</div>
        <div class="hint">Open booking requests from customers</div>
      </div>
      <div class="db-stat">
        <div class="label">Jobs</div>
        <div class="value">0</div>
        <div class="hint">Assigned after activation</div>
      </div>
      <div class="db-stat">
        <div class="label">Coverage</div>
        <div class="value" style="font-size: 14px;">{{ profile.service_areas || 'Not set' }}</div>
        <div class="hint">Service areas</div>
      </div>
    </div>

    <div class="db-action-grid">
      <RouterLink class="db-action" to="/dashboard/provider/requests">
        <strong>Review booking requests</strong>
        <span>See customer service needs and filter by status</span>
      </RouterLink>
      <RouterLink class="db-action" to="/dashboard/provider/jobs">
        <strong>My jobs</strong>
        <span>Assigned work after activation</span>
      </RouterLink>
      <RouterLink class="db-action" to="/dashboard/provider/profile">
        <strong>Update profile</strong>
        <span>Category, areas, and contact details</span>
      </RouterLink>
      <RouterLink class="db-action" to="/catalogue">
        <strong>Browse catalogue</strong>
        <span>See where your specialization fits</span>
      </RouterLink>
    </div>
  </div>
</template>
