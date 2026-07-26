<script setup>
import { computed, ref } from 'vue'
import DashboardShell from '../components/DashboardShell.vue'
import { useAuth } from '../stores/auth'
import {
  businessFlow,
  categoryRevenueModel,
  commissionFlow,
  commissionTiers,
  enablers,
  growthBars,
  platformHighlights,
  platformStats,
  revenueStreams,
} from '../data/dashboardContent'

const { state } = useAuth()
const section = ref('overview')

const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => (profile.value.status || 'pending_verification').replaceAll('_', ' '))
const isPending = computed(() => {
  const s = String(profile.value.status || 'pending_verification')
  return !['active', 'activated', 'verified'].includes(s)
})

const navItems = [
  { id: 'overview', label: 'Overview', icon: '▣' },
  { id: 'earnings', label: 'Earnings Model', icon: '◈' },
  { id: 'profile', label: 'Profile', icon: '◎' },
]

const pageTitle = computed(() => {
  if (section.value === 'earnings') return 'Earnings & Commission Model'
  if (section.value === 'profile') return 'Professional Profile'
  return state.user?.name || 'Provider workspace'
})

const pageSubtitle = computed(() => {
  if (section.value === 'earnings') return 'Take rates, payouts, and growth outlook'
  if (section.value === 'profile') return 'Details from your provider registration'
  const cat = profile.value.category || 'Professional'
  const sub = profile.value.subcategory ? ` · ${profile.value.subcategory}` : ''
  return `${cat}${sub}`
})

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
    role="provider"
    :title="pageTitle"
    :subtitle="pageSubtitle"
    :nav-items="navItems"
    v-model:section="section"
  >
    <template #actions>
      <RouterLink to="/catalogue" class="db-btn db-btn-ghost">Catalogue</RouterLink>
      <RouterLink to="/forms/company" class="db-btn db-btn-gold">Company</RouterLink>
    </template>

    <template v-if="section === 'overview'">
      <div class="db-hero-strip">
        <div>
          <div class="eyebrow">Provider dashboard</div>
          <h2>Grow with Pakistan’s trusted services network.</h2>
          <p>Track verification, understand commission tiers, and prepare for activated job flow across 15+ categories.</p>
        </div>
        <div class="hero-pills">
          <span>Verified Network</span>
          <span>Weekly Payouts</span>
          <span>Digital Reputation</span>
        </div>
      </div>

      <div class="db-stats">
        <div class="db-stat">
          <div class="label">Verification</div>
          <div class="value" :class="isPending ? 'gold' : 'accent'" style="font-size: 17px; text-transform: capitalize;">
            {{ statusLabel }}
          </div>
          <div class="hint">Registration → Verification → Training → Activation</div>
        </div>
        <div class="db-stat">
          <div class="label">Assigned jobs</div>
          <div class="value accent">0</div>
          <div class="hint">Live after activation</div>
        </div>
        <div class="db-stat">
          <div class="label">Category</div>
          <div class="value" style="font-size: 15px;">{{ profile.category || 'Pending' }}</div>
          <div class="hint">{{ profile.subcategory || 'Specialization pending' }}</div>
        </div>
        <div class="db-stat">
          <div class="label">Coverage</div>
          <div class="value" style="font-size: 14px;">{{ profile.service_areas || 'Not set' }}</div>
          <div class="hint">Service areas on file</div>
        </div>
      </div>

      <div class="db-layout-a">
        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Marketplace growth</h2>
              <p>Illustrative revenue scale (relative)</p>
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
              <h2>Platform revenue mix</h2>
              <p>Where marketplace income comes from</p>
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
              <h2>Onboarding pipeline</h2>
              <p>Your path to activation</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-profile-list">
              <div class="db-profile-row">
                <span class="k">1. Registration</span>
                <span class="v"><span class="db-status">Complete</span></span>
              </div>
              <div class="db-profile-row">
                <span class="k">2. Verification</span>
                <span class="v">
                  <span class="db-status" :class="isPending ? 'warn' : ''">
                    {{ isPending ? 'In progress' : 'Complete' }}
                  </span>
                </span>
              </div>
              <div class="db-profile-row">
                <span class="k">3. Training</span>
                <span class="v"><span class="db-status warn">Queued</span></span>
              </div>
              <div class="db-profile-row">
                <span class="k">4. Activation</span>
                <span class="v"><span class="db-status warn">Pending</span></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Commission structure</h2>
            <p>Customer pays → provider earns → Taskora commission → payout</p>
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
          <div class="db-table-wrap" style="margin-top: 16px;">
            <table class="db-dense-table">
              <thead>
                <tr><th>Service value (PKR)</th><th>Taskora take rate</th></tr>
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

    <template v-else-if="section === 'earnings'">
      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Revenue model by category</h2>
            <p>Where your specialization sits in the Taskora model</p>
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

      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Jobs board</h2>
            <p>Assigned work appears after activation</p>
          </div>
        </div>
        <div class="db-panel-body">
          <div class="db-empty">
            No jobs assigned yet.
            <div style="margin-top: 8px; font-size: 12.5px;">
              Status: <strong style="text-transform: capitalize;">{{ statusLabel }}</strong>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="db-grid-2">
        <div class="db-panel">
          <div class="db-panel-head"><div><h2>Contact</h2><p>Account details</p></div></div>
          <div class="db-panel-body">
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Name</span><span class="v">{{ state.user?.name }}</span></div>
              <div class="db-profile-row"><span class="k">Email</span><span class="v">{{ state.user?.email }}</span></div>
              <div class="db-profile-row"><span class="k">Phone</span><span class="v">{{ state.user?.phone || 'Not set' }}</span></div>
              <div class="db-profile-row"><span class="k">Role</span><span class="v">Provider</span></div>
            </div>
          </div>
        </div>
        <div class="db-panel">
          <div class="db-panel-head"><div><h2>Service profile</h2><p>From provider signup</p></div></div>
          <div class="db-panel-body">
            <div class="db-profile-list">
              <div class="db-profile-row"><span class="k">Category</span><span class="v">{{ profile.category || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Subcategory</span><span class="v">{{ profile.subcategory || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Service areas</span><span class="v">{{ profile.service_areas || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Experience</span><span class="v">{{ profile.experience || '—' }}</span></div>
              <div class="db-profile-row"><span class="k">Status</span><span class="v" style="text-transform: capitalize;">{{ statusLabel }}</span></div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
