<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardShell from '../components/DashboardShell.vue'
import { useAuth } from '../stores/auth'
import { apiMyForms } from '../api/client'
import {
  businessFlow,
  categoryRevenueModel,
  commissionFlow,
  commissionTiers,
  enablers,
  feasibilityRows,
  growthBars,
  platformHighlights,
  platformStats,
  revenueStreams,
} from '../data/dashboardContent'

const { state } = useAuth()
const submissions = ref([])
const section = ref('overview')

const navItems = [
  { id: 'overview', label: 'Overview', icon: '▣' },
  { id: 'activity', label: 'My Activity', icon: '☰' },
  { id: 'model', label: 'Platform Model', icon: '◇' },
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
  submissions.value.filter((s) => !['closed', 'resolved', 'completed'].includes(String(s.status || '').toLowerCase())).length,
)
const supportCount = computed(() =>
  submissions.value.filter((s) => ['feedback', 'complaint', 'refund', 'insurance'].includes(s.type)).length,
)

const pageTitle = computed(() => {
  if (section.value === 'activity') return 'My Activity Ledger'
  if (section.value === 'model') return 'Taskora Platform Model'
  return `Welcome, ${state.user?.name?.split(' ')[0] || 'Customer'}`
})

const pageSubtitle = computed(() => {
  if (section.value === 'activity') return 'Bookings, support forms, and request trail'
  if (section.value === 'model') return 'How Taskora earns, pays professionals, and scales'
  return "Pakistan's trusted digital services workspace"
})

function statusClass(status) {
  const s = String(status || '').toLowerCase()
  if (['pending', 'submitted', 'open', 'in_progress'].includes(s)) return 'warn'
  return ''
}

const donutGradient = computed(() => {
  const colors = ['#0e8f57', '#065f46', '#d4af37', '#2bb673', '#8fbc8f', '#04382b']
  let acc = 0
  const stops = []
  revenueStreams.forEach((s, i) => {
    const next = acc + s.pct
    stops.push(`${colors[i % colors.length]} ${acc}% ${next}%`)
    acc = next
  })
  return `conic-gradient(${stops.join(', ')})`
})
</script>

<template>
  <DashboardShell
    role="customer"
    :title="pageTitle"
    :subtitle="pageSubtitle"
    :nav-items="navItems"
    v-model:section="section"
  >
    <template #actions>
      <RouterLink to="/forms/booking" class="db-btn db-btn-gold">Book service</RouterLink>
    </template>

    <!-- OVERVIEW -->
    <template v-if="section === 'overview'">
      <div class="db-hero-strip">
        <div>
          <div class="eyebrow">Customer dashboard</div>
          <h2>One app. Every service. Trusted delivery.</h2>
          <p>Browse 15+ categories, book verified professionals, and track every request from one workspace.</p>
        </div>
        <div class="hero-pills">
          <span>Verified Pros</span>
          <span>Secure Payments</span>
          <span>Live Tracking</span>
        </div>
      </div>

      <div class="db-stats">
        <div class="db-stat">
          <div class="label">My bookings</div>
          <div class="value accent">{{ bookingCount }}</div>
          <div class="hint">Service requests submitted</div>
        </div>
        <div class="db-stat">
          <div class="label">Open items</div>
          <div class="value">{{ openCount }}</div>
          <div class="hint">Awaiting follow-up</div>
        </div>
        <div class="db-stat">
          <div class="label">Support cases</div>
          <div class="value">{{ supportCount }}</div>
          <div class="hint">Feedback & claims</div>
        </div>
        <div class="db-stat">
          <div class="label">Account</div>
          <div class="value gold">Active</div>
          <div class="hint">Customer access</div>
        </div>
      </div>

      <div class="db-layout-a">
        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Platform growth outlook</h2>
              <p>Illustrative 5-year revenue trajectory (PKR)</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-bars">
              <div v-for="bar in growthBars" :key="bar.year" class="db-bar-col">
                <div class="bar" :style="{ height: `${bar.value}%` }" />
                <span>{{ bar.year }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Revenue streams</h2>
              <p>How Taskora monetizes the marketplace</p>
            </div>
          </div>
          <div class="db-panel-body db-donut-row">
            <div class="db-donut" :style="{ background: donutGradient }" />
            <ul class="db-legend">
              <li v-for="s in revenueStreams" :key="s.label">
                <span>{{ s.label }}</span>
                <strong>{{ s.pct }}%</strong>
              </li>
            </ul>
          </div>
        </div>

        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Commission flow</h2>
              <p>Transparent money movement on every job</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-flow">
              <div v-for="(step, i) in commissionFlow" :key="step" class="db-flow-step">
                <div class="bubble">{{ i + 1 }}</div>
                <div class="txt">{{ step }}</div>
                <div v-if="i < commissionFlow.length - 1" class="arrow">→</div>
              </div>
            </div>
            <table class="db-dense-table" style="margin-top: 16px;">
              <thead>
                <tr><th>Service value (PKR)</th><th>Take rate</th></tr>
              </thead>
              <tbody>
                <tr v-for="tier in commissionTiers" :key="tier.value">
                  <td>{{ tier.value }}</td>
                  <td><strong>{{ tier.rate }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Recent activity ledger</h2>
            <p>Your latest form submissions</p>
          </div>
          <button type="button" class="db-btn db-btn-ghost" @click="section = 'activity'">View all</button>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div v-if="!submissions.length" class="db-empty">No activity yet — book a service to start your ledger.</div>
          <div v-else class="db-table-wrap">
            <table class="db-dense-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Particulars</th>
                  <th>Ref / ID</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in submissions.slice(0, 6)" :key="item.id">
                  <td>{{ new Date(item.created_at).toLocaleDateString() }}</td>
                  <td>Customer {{ item.type }} request</td>
                  <td>{{ item.reference }}</td>
                  <td style="text-transform: capitalize;">{{ item.type }}</td>
                  <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="db-highlights">
        <div v-for="h in platformHighlights" :key="h.label" class="db-hl">
          <strong>{{ h.value }}</strong>
          <span>{{ h.label }}</span>
        </div>
      </div>

      <div class="db-footer-bar">
        <div v-for="s in platformStats" :key="s.label" class="db-footer-stat">
          <strong>{{ s.value }}</strong>
          <span>{{ s.label }}</span>
        </div>
      </div>
    </template>

    <!-- ACTIVITY -->
    <template v-else-if="section === 'activity'">
      <div class="db-stats">
        <div class="db-stat">
          <div class="label">Bookings</div>
          <div class="value accent">{{ bookingCount }}</div>
        </div>
        <div class="db-stat">
          <div class="label">Support</div>
          <div class="value">{{ supportCount }}</div>
        </div>
        <div class="db-stat">
          <div class="label">Open</div>
          <div class="value gold">{{ openCount }}</div>
        </div>
        <div class="db-stat">
          <div class="label">Quick link</div>
          <div class="value" style="font-size: 15px;">Book now</div>
          <RouterLink to="/forms/booking" class="db-btn db-btn-primary" style="margin-top: 10px;">New booking</RouterLink>
        </div>
      </div>

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Full activity trail</h2>
            <p>Ledger-style history of every request</p>
          </div>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div v-if="!submissions.length" class="db-empty">No submissions yet.</div>
          <div v-else class="db-table-wrap">
            <table class="db-dense-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Particulars</th>
                  <th>Ref / Order ID</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in submissions" :key="item.id">
                  <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                  <td>Customer {{ item.type }} submission</td>
                  <td>{{ item.reference }}</td>
                  <td style="text-transform: capitalize;">{{ item.type }}</td>
                  <td><span class="db-status" :class="statusClass(item.status)">{{ item.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="db-action-grid" style="margin-top: 8px;">
        <RouterLink class="db-action" to="/forms/feedback"><strong>Feedback</strong><span>Rate a completed job</span></RouterLink>
        <RouterLink class="db-action" to="/forms/complaint"><strong>Complaint</strong><span>Raise a support case</span></RouterLink>
        <RouterLink class="db-action" to="/forms/refund"><strong>Refund</strong><span>Request a refund review</span></RouterLink>
        <RouterLink class="db-action" to="/forms/insurance"><strong>Insurance</strong><span>Submit a claim form</span></RouterLink>
      </div>
    </template>

    <!-- MODEL -->
    <template v-else>
      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Financial feasibility · 5-year projection</h2>
            <p>Illustrative platform outlook (not your personal balances)</p>
          </div>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div class="db-table-wrap">
            <table class="db-dense-table">
              <thead>
                <tr>
                  <th>Year</th>
                  <th>GMV (PKR)</th>
                  <th>Orders</th>
                  <th>Take rate</th>
                  <th>Revenue</th>
                  <th>Gross profit</th>
                  <th>NPM</th>
                  <th>Net profit</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in feasibilityRows" :key="row.year">
                  <td>{{ row.year }}</td>
                  <td>{{ row.gmv }}</td>
                  <td>{{ row.orders }}</td>
                  <td>{{ row.take }}</td>
                  <td>{{ row.revenue }}</td>
                  <td>{{ row.gp }}</td>
                  <td>{{ row.npm }}</td>
                  <td><strong>{{ row.np }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Revenue model by category</h2>
            <p>How each service group contributes</p>
          </div>
        </div>
        <div class="db-panel-body" style="padding: 0;">
          <div class="db-table-wrap">
            <table class="db-dense-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Revenue model</th>
                  <th>Avg take rate</th>
                  <th>Additional opportunities</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in categoryRevenueModel" :key="row.category">
                  <td>{{ row.category }}</td>
                  <td>{{ row.model }}</td>
                  <td>{{ row.rate }}</td>
                  <td>{{ row.extra }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="db-layout-b">
        <div class="db-panel">
          <div class="db-panel-head"><div><h2>Business model flow</h2></div></div>
          <div class="db-panel-body">
            <div class="db-flow wrap">
              <div v-for="(step, i) in businessFlow" :key="step" class="db-flow-step">
                <div class="bubble">{{ i + 1 }}</div>
                <div class="txt">{{ step }}</div>
                <div v-if="i < businessFlow.length - 1" class="arrow">→</div>
              </div>
            </div>
          </div>
        </div>
        <div class="db-panel">
          <div class="db-panel-head"><div><h2>Key enablers</h2></div></div>
          <div class="db-panel-body">
            <div class="db-enablers">
              <div v-for="e in enablers" :key="e" class="db-enabler">{{ e }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="db-footer-bar">
        <div v-for="s in platformStats" :key="s.label" class="db-footer-stat">
          <strong>{{ s.value }}</strong>
          <span>{{ s.label }}</span>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
