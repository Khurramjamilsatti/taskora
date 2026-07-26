<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'

const router = useRouter()
const { state, logout } = useAuth()

const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => profile.value.status || 'pending_verification')

async function handleLogout() {
  await logout()
  router.push('/login')
}
</script>

<template>
  <div class="dash">
    <header class="dash-nav">
      <RouterLink to="/" class="dash-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <span>TASKORA</span>
      </RouterLink>
      <div class="dash-nav-actions">
        <span class="role-chip provider">Provider</span>
        <button class="btn btn-ghost btn-mini" @click="handleLogout">Log out</button>
      </div>
    </header>

    <main class="dash-body">
      <p class="tag">Provider dashboard</p>
      <h1>{{ state.user?.name }}</h1>
      <p class="dash-sub">
        {{ profile.category || 'Professional' }}
        <span v-if="profile.subcategory"> · {{ profile.subcategory }}</span>
      </p>

      <div class="dash-grid">
        <div class="dash-card">
          <h3>Verification status</h3>
          <p class="big status">{{ statusLabel.replaceAll('_', ' ') }}</p>
          <p class="muted">Registration → Verification → Training → Activation</p>
        </div>
        <div class="dash-card">
          <h3>Jobs</h3>
          <p class="big">0</p>
          <p class="muted">Assigned jobs will appear here after activation.</p>
        </div>
        <div class="dash-card">
          <h3>Profile</h3>
          <p>{{ state.user?.email }}</p>
          <p class="muted">{{ state.user?.phone || 'No phone on file' }}</p>
          <p class="muted">{{ profile.service_areas || 'Add service areas after verification' }}</p>
        </div>
        <div class="dash-card">
          <h3>Grow with Taskora</h3>
          <p class="muted">See where you fit in the master catalogue.</p>
          <RouterLink class="dash-link" to="/catalogue">Browse catalogue →</RouterLink>
          <RouterLink class="dash-link" to="/forms/company">Register a company →</RouterLink>
        </div>
      </div>
    </main>
  </div>
</template>
