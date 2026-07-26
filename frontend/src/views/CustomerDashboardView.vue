<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { apiMyForms } from '../api/client'

const router = useRouter()
const { state, logout } = useAuth()
const submissions = ref([])

onMounted(async () => {
  try {
    const res = await apiMyForms()
    submissions.value = res.data || []
  } catch {
    submissions.value = []
  }
})

const bookingCount = computed(() => submissions.value.filter((s) => s.type === 'booking').length)

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
        <span class="role-chip">Customer</span>
        <button class="btn btn-ghost btn-mini" @click="handleLogout">Log out</button>
      </div>
    </header>

    <main class="dash-body">
      <p class="tag">Customer dashboard</p>
      <h1>Welcome back, {{ state.user?.name }}</h1>
      <p class="dash-sub">Manage bookings, feedback, and support from one place.</p>

      <div class="dash-grid">
        <div class="dash-card">
          <h3>Bookings</h3>
          <p class="big">{{ bookingCount }}</p>
          <p class="muted">Submitted booking requests</p>
          <RouterLink class="dash-link" to="/forms/booking">Book a service →</RouterLink>
        </div>
        <div class="dash-card">
          <h3>Catalogue</h3>
          <p class="muted">Browse 300+ verified specializations.</p>
          <RouterLink class="dash-link" to="/catalogue">Open catalogue →</RouterLink>
        </div>
        <div class="dash-card">
          <h3>Support</h3>
          <p class="muted">Feedback, complaints, refunds, insurance claims.</p>
          <div class="dash-links">
            <RouterLink to="/forms/feedback">Feedback</RouterLink>
            <RouterLink to="/forms/complaint">Complaint</RouterLink>
            <RouterLink to="/forms/refund">Refund</RouterLink>
            <RouterLink to="/forms/insurance">Insurance</RouterLink>
          </div>
        </div>
      </div>

      <section class="dash-section">
        <h2>Recent submissions</h2>
        <div v-if="!submissions.length" class="empty">No submissions yet.</div>
        <table v-else class="dash-table">
          <thead>
            <tr><th>Reference</th><th>Type</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <tr v-for="item in submissions" :key="item.id">
              <td>{{ item.reference }}</td>
              <td>{{ item.type }}</td>
              <td>{{ item.status }}</td>
              <td>{{ new Date(item.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>
