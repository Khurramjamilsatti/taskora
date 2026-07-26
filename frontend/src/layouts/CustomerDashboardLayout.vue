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
      { to: '/forms/feedback', label: 'Feedback', icon: '★' },
      { to: '/forms/complaint', label: 'Complaint', icon: '!' },
      { to: '/forms/refund', label: 'Refund', icon: '↺' },
      { to: '/forms/insurance', label: 'Insurance', icon: '◈' },
    ],
  },
  {
    label: 'More',
    items: [
      { to: '/catalogue', label: 'Catalogue', icon: '◇' },
      { to: '/', label: 'Website', icon: '←' },
    ],
  },
]
</script>

<template>
  <DashboardShell role="customer" :nav-items="navItems">
    <RouterView />
  </DashboardShell>
</template>
