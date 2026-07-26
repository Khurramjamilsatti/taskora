<script setup>
import DashboardShell from '../components/DashboardShell.vue'

const navItems = [
  {
    label: 'Work',
    items: [
      { to: '/dashboard/provider', label: 'Overview', icon: '▣' },
      { to: '/dashboard/provider/requests', label: 'Booking Requests', icon: '◷', match: '/dashboard/provider/requests' },
      { to: '/dashboard/provider/jobs', label: 'My Jobs', icon: '☰', match: '/dashboard/provider/jobs' },
      { to: '/dashboard/provider/settings/profile', label: 'Profile', icon: '◎', match: '/dashboard/provider/settings' },
    ],
  },
]
</script>

<template>
  <DashboardShell role="provider" :nav-items="navItems">
    <RouterView />
  </DashboardShell>
</template>
