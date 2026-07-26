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
    label: 'Support',
    items: [
      { to: '/dashboard/provider/support/complaint', label: 'Complaint', icon: '!', match: '/dashboard/provider/support/complaint' },
      { to: '/dashboard/provider/support/insurance', label: 'Insurance', icon: '◈', match: '/dashboard/provider/support/insurance' },
    ],
  },
  {
    label: 'Growth',
    items: [
      { to: '/dashboard/provider/support/company', label: 'Company Signup', icon: '+', match: '/dashboard/provider/support/company' },
    ],
  },
]
</script>

<template>
  <DashboardShell role="provider" :nav-items="navItems">
    <RouterView />
  </DashboardShell>
</template>
