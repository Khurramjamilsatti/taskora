<script setup>
import { computed, ref } from 'vue'
import { bookableCategories, flattenServices } from '../../data/bookableServices'

const query = ref('')
const category = ref('All')

const categoryOptions = computed(() => ['All', ...bookableCategories.map((c) => c.title)])

const services = computed(() => {
  const q = query.value.trim().toLowerCase()
  return flattenServices().filter((item) => {
    if (category.value !== 'All' && item.category !== category.value) return false
    if (!q) return true
    return (
      item.service.toLowerCase().includes(q)
      || item.category.toLowerCase().includes(q)
    )
  })
})

const grouped = computed(() => {
  const map = new Map()
  services.value.forEach((item) => {
    if (!map.has(item.category)) map.set(item.category, [])
    map.get(item.category).push(item.service)
  })
  return [...map.entries()].map(([title, list]) => ({ title, services: list }))
})

function bookTo(cat, service) {
  return {
    path: '/dashboard/customer/book',
    query: { category: cat, service },
  }
}
</script>

<template>
  <div>
    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>All services</h2>
          <p>Select a service to open the booking form</p>
        </div>
        <RouterLink to="/dashboard/customer/bookings" class="db-btn db-btn-ghost">My Bookings</RouterLink>
      </div>
      <div class="db-panel-body">
        <div class="bk-filters">
          <input v-model="query" type="search" placeholder="Search services…" class="bk-input" />
          <select v-model="category" class="bk-input">
            <option v-for="opt in categoryOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="!grouped.length" class="db-empty">No services match your filters.</div>

    <div v-for="group in grouped" :key="group.title" class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>{{ group.title }}</h2>
          <p>{{ group.services.length }} service{{ group.services.length === 1 ? '' : 's' }}</p>
        </div>
      </div>
      <div class="db-panel-body">
        <div class="svc-grid">
          <RouterLink
            v-for="service in group.services"
            :key="service"
            class="svc-card"
            :to="bookTo(group.title, service)"
          >
            <strong>{{ service }}</strong>
            <span>Book now →</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
