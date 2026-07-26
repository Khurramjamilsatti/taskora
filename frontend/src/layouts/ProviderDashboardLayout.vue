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
  {
    label: 'Growth',
    items: [
      { to: '/catalogue', label: 'Catalogue', icon: '◇' },
      { to: '/forms/company', label: 'Company Signup', icon: '+' },
      { to: '/', label: 'Website', icon: '←' },
    ],
  },
]
</script>

<template>
  <DashboardShell role="provider" :nav-items="navItems">
    <RouterView />
  </DashboardShell>
</template>
