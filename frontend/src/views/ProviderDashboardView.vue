<script setup>
import { computed, ref } from 'vue'
import DashboardShell from '../components/DashboardShell.vue'
import { useAuth } from '../stores/auth'

const { state } = useAuth()
const section = ref('overview')

const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => (profile.value.status || 'pending_verification').replaceAll('_', ' '))
const isPending = computed(() => {
  const s = String(profile.value.status || 'pending_verification')
  return !['active', 'activated', 'verified'].includes(s)
})

const navSections = [
  {
    label: 'Main',
    items: [
      { id: 'overview', label: 'Overview', icon: '▣' },
      { id: 'jobs', label: 'Jobs', icon: '◷' },
      { id: 'profile', label: 'Profile', icon: '◎' },
    ],
  },
  {
    label: 'Growth',
    items: [
      { href: '/catalogue', label: 'Catalogue', icon: '◇' },
      { href: '/forms/company', label: 'Company signup', icon: '+' },
    ],
  },
  {
    label: 'Site',
    items: [{ href: '/', label: 'Back to website', icon: '←' }],
  },
]

const pageTitle = computed(() => {
  if (section.value === 'jobs') return 'Jobs'
  if (section.value === 'profile') return 'Professional profile'
  return state.user?.name || 'Provider workspace'
})

const pageSubtitle = computed(() => {
  if (section.value === 'jobs') return 'Assigned work appears here after activation.'
  if (section.value === 'profile') return 'Details captured during provider registration.'
  const cat = profile.value.category || 'Professional'
  const sub = profile.value.subcategory ? ` · ${profile.value.subcategory}` : ''
  return `${cat}${sub}`
})
</script>

<template>
  <DashboardShell
    role="provider"
    :title="pageTitle"
    :subtitle="pageSubtitle"
    :nav-sections="navSections"
    v-model:section="section"
  >
    <template #actions>
      <RouterLink to="/catalogue" class="db-btn db-btn-ghost">Catalogue</RouterLink>
      <RouterLink to="/forms/company" class="db-btn db-btn-gold">Company</RouterLink>
    </template>

    <template v-if="section === 'overview'">
      <div class="db-stats">
        <div class="db-stat">
          <div class="label">Verification</div>
          <div class="value" :class="isPending ? 'gold' : 'accent'" style="font-size: 18px; text-transform: capitalize;">
            {{ statusLabel }}
          </div>
          <div class="hint">Registration → Verification → Training → Activation</div>
        </div>
        <div class="db-stat">
          <div class="label">Assigned jobs</div>
          <div class="value accent">0</div>
          <div class="hint">Available after activation</div>
        </div>
        <div class="db-stat">
          <div class="label">Category</div>
          <div class="value" style="font-size: 16px;">{{ profile.category || 'Pending' }}</div>
          <div class="hint">{{ profile.subcategory || 'Specialization pending' }}</div>
        </div>
        <div class="db-stat">
          <div class="label">Coverage</div>
          <div class="value" style="font-size: 15px;">{{ profile.service_areas || 'Not set' }}</div>
          <div class="hint">Service areas on file</div>
        </div>
      </div>

      <div class="db-grid-2">
        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Onboarding pipeline</h2>
              <p>Where you are in the Taskora provider journey</p>
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

        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Grow with Taskora</h2>
              <p>Tools while verification completes</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-action-grid">
              <RouterLink class="db-action" to="/catalogue">
                <strong>Browse catalogue</strong>
                <span>See where your specialization fits</span>
              </RouterLink>
              <RouterLink class="db-action" to="/forms/company">
                <strong>Register a company</strong>
                <span>Corporate / team onboarding form</span>
              </RouterLink>
              <button type="button" class="db-action" @click="section = 'profile'">
                <strong>Review profile</strong>
                <span>Confirm contact and service details</span>
              </button>
              <button type="button" class="db-action" @click="section = 'jobs'">
                <strong>Jobs board</strong>
                <span>Opens after your account is activated</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else-if="section === 'jobs'">
      <div class="db-panel">
        <div class="db-panel-head">
          <div>
            <h2>Assigned jobs</h2>
            <p>Live jobs will appear here once verification and training are complete.</p>
          </div>
        </div>
        <div class="db-panel-body">
          <div class="db-empty">
            No jobs assigned yet.
            <div style="margin-top: 8px; font-size: 12.5px;">
              Current status: <strong style="text-transform: capitalize;">{{ statusLabel }}</strong>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="db-grid-2">
        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Contact</h2>
              <p>Account credentials and reachability</p>
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
                <span class="v">Provider</span>
              </div>
            </div>
          </div>
        </div>

        <div class="db-panel">
          <div class="db-panel-head">
            <div>
              <h2>Service profile</h2>
              <p>Captured during provider signup</p>
            </div>
          </div>
          <div class="db-panel-body">
            <div class="db-profile-list">
              <div class="db-profile-row">
                <span class="k">Category</span>
                <span class="v">{{ profile.category || '—' }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Subcategory</span>
                <span class="v">{{ profile.subcategory || '—' }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Service areas</span>
                <span class="v">{{ profile.service_areas || '—' }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Experience</span>
                <span class="v">{{ profile.experience || '—' }}</span>
              </div>
              <div class="db-profile-row">
                <span class="k">Status</span>
                <span class="v" style="text-transform: capitalize;">{{ statusLabel }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
