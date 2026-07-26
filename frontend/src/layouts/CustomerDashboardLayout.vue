<script setup>
import DashboardShell from '../components/DashboardShell.vue'

const navItems = [
  {
    label: 'Booking',
    items: [
      { to: '/dashboard/customer', label: 'Overview', icon: '▣', match: null },
      { to: '/dashboard/customer/services', label: 'Book a Service', icon: '+', match: '/dashboard/customer/services' },
      { to: '/dashboard/customer/bookings', label: 'My Bookings', icon: '☰', match: '/dashboard/customer/bookings' },
      { to: '/dashboard/customer/book', label: 'New Booking Form', icon: '✎' },
    ],
  },
  {
    label: 'Support',
    items: [
      { to: '/dashboard/customer/support/complaint', label: 'Complaint', icon: '!', match: '/dashboard/customer/support/complaint' },
      { to: '/dashboard/customer/support/refund', label: 'Refund', icon: '↺', match: '/dashboard/customer/support/refund' },
      { to: '/dashboard/customer/support/insurance', label: 'Insurance', icon: '◈', match: '/dashboard/customer/support/insurance' },
    ],
  },
]
</script>

<template>
  <DashboardShell role="customer" :nav-items="navItems">
    <RouterView />
  </DashboardShell>
</template>
