<script setup>
import { computed } from 'vue'
import { useAuth } from '../../stores/auth'

const { state } = useAuth()
const profile = computed(() => state.user?.profile || {})
const statusLabel = computed(() => (profile.value.status || 'pending_verification').replaceAll('_', ' '))
</script>

<template>
  <div class="db-grid-2">
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>Contact</h2>
          <p>Account details</p>
        </div>
      </div>
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
      <div class="db-panel-head">
        <div>
          <h2>Service profile</h2>
          <p>From provider signup</p>
        </div>
      </div>
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
