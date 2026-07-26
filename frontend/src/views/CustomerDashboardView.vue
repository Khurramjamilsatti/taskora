<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardShell from '../components/DashboardShell.vue'
import { useAuth } from '../stores/auth'
import { apiMyForms } from '../api/client'

const { state } = useAuth()
const submissions = ref([])
const section = ref('overview')

const navSections = [
  {
    label: 'Main',
    items: [
      { id: 'overview', label: 'Overview', icon: '▣' },
      { id: 'bookings', label: 'Bookings', icon: '◷' },
      { id: 'submissions', label: 'Submissions', icon: '☰' },
    ],
  },
  {
    label: 'Services',
    items: [
      { href: '/catalogue', label: 'Catalogue', icon: '◇' },
      { href: '/forms/booking', label: 'Book a service', icon: '+' },
    ],
  },
  {
    label: 'Support',
    items: [
      { href: '/forms/feedback', label: 'Feedback', icon: '★' },
      { href: '/forms/complaint', label: 'Complaint', icon: '!' },
      { href: '/forms/refund', label: 'Refund', icon: '↺' },
      { href: '/forms/insurance', label: 'Insurance', icon: '◈' },
    ],
  },
  {
    label: 'Site',
    items: [{ href: '/', label: 'Back to website', icon: '←' }],
  },
]

onMounted(async () => {
  try {
    const res = await apiMyForms()
    submissions.value = res.data || []
  } catch {
    submissions.value = []
  }
})

const bookingCount = computed(() => submissions.value.filter((s) => s.type === 'booking').length)
const openCount = computed(() =>
  submissions.value.filter((s) => !['closed', 'resolved', 'completed'].includes(s.status)).length,
)
const supportCount = computed(() =>
  submissions.value.filter((s) => ['feedback', 'complaint', 'refund', 'insurance'].includes(s.type)).length,
)

const pageTitle = computed(() => {
  if (section.value === 'bookings') return 'Bookings'
  if (section.value === 'submissions') return 'Submissions'
  return `Welcome back, ${state.user?.name?.split(' ')[0] || 'there'}`
})

const pageSubtitle = computed(() => {
  if (section.value === 'bookings') return 'Track and create service booking requests.'
  if (section.value === 'submissions') return 'All forms you have submitted through Taskora.'
  return 'Your customer workspace for bookings, catalogue, and support.'
})

function statusClass(status) {
  const s = String(status || '').toLowerCase()
  if (['pending', 'submitted', 'open', 'in_progress'].includes(s)) return 'warn'
  return ''
}
</script>

<template>
  <DashboardShell
    role="customer"
    :title="pageTitle"
    :subtitle="pageSubtitle"
    :nav-sections="navSections"
    v-model:section="section"
  >
    <template #actions>
      <RouterLink to="/forms/booking" class="db-btn db-btn-gold">Book service</RouterLink>
    </template>

    <template v-if="section === 'overview'">
      <div class="db-stats">
        <div class="db-stat">
          <div class="label">Bookings</div>
          <div class="value accent">{{ bookingCount }}</div>
          <div class="hint">Service requests submitted</div>
        </div>
        <div class="db-stat">
          <div class="label">Open items</div>
          <div class="value">{{ openCount }}</div>
          <div class="hint">Awaiting follow-up</div>
        </div>
        <div class="db-stat">
          <div class="label">Support</div>
          <div class="value">{{ supportCount }}</div>
          <div class="hint">Feedback & claims</div>
        </div>
        <div class="db-stat">
          <div class="label">Account</div>
          <div class="value gold">Active</div>
          <div class="hint">Customer verified access</div>
        </div>
      </div>

      <div class="db-grid-2">
        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Quick actions</h2>
              <p>Common tasks for your account</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-action-grid">
              <RouterLink class="db-action" to="/forms/booking">
                <strong>Book a professional</strong>
                <span>Submit a new service request</span>
              </RouterLink>
              <RouterLink class="db-action" to="/catalogue">
                <strong>Browse catalogue</strong>
                <span>300+ verified specializations</span>
              </RouterLink>
              <RouterLink class="db-action" to="/forms/feedback">
                <strong>Leave feedback</strong>
                <span>Rate a completed job</span>
              </RouterLink>
              <RouterLink class="db-action" to="/forms/complaint">
                <strong>Raise a complaint</strong>
                <span>Get support from Taskora</span>
              </RouterLink>
            </div>
          </div>
        </div>

        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Account</h2>
              <p>Your customer profile</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-profile-list">
              <div class="db-profile-row">
                <span class="k">Name</span>
                <span class="v">{{ state.user?.name }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Email</span>
                <span class="v">{{ state.user?.email }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Phone</span>
                <span class="v">{{ state.user?.phone || 'Not set' }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Role</span>
                <span class="v">Customer</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Recent submissions</h2>
            <p>Latest activity across forms</p>
          </div>
          <button type="button" class="db-btn db-btn-ghost" @click="section = 'submissions'">
            View all
          </button>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div v-if="!submissions.length" class="db-empty">No submissions yet. Book a service to get started.</div>
          <div v-else class="db-table-wrap">
            <table class="db-table">
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in submissions.slice(0, 5)" :key="item.id">
                  <td>{{ item.reference }}</td>
                  <td style="text-transform: capitalize;">{{ item.type }}</td>
                  <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
                  <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <template v-else-if="section === 'bookings'">
      <div class="db-stats">
        <div class="db-stat">
          <div class="label">Total bookings</div>
          <div class="value accent">{{ bookingCount }}</div>
          <div class="hint">All booking form submissions</div>
        </div>
        <div class="db-stat">
          <div class="label">Next step</div>
          <div class="value gold" style="font-size: 18px;">Book now</div>
          <div class="hint">Create a new service request</div>
        </div>
      </div>
      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Your bookings</h2>
            <p>Booking requests submitted from your account</p>
          </div>
          <RouterLink to="/forms/booking" class="db-btn db-btn-primary">New booking</RouterLink>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div v-if="!bookingCount" class="db-empty">No bookings yet.</div>
          <div v-else class="db-table-wrap">
            <table class="db-table">
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in submissions.filter((s) => s.type === 'booking')" :key="item.id">
                  <td>{{ item.reference }}</td>
                  <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
                  <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>All submissions</h2>
            <p>Bookings, feedback, complaints, refunds, and insurance</p>
          </div>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div v-if="!submissions.length" class="db-empty">No submissions yet.</div>
          <div v-else class="db-table-wrap">
            <table class="db-table">
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in submissions" :key="item.id">
                  <td>{{ item.reference }}</td>
                  <td style="text-transform: capitalize;">{{ item.type }}</td>
                  <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
                  <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
