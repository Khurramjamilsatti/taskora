<script setup>
import SectionHead from './SectionHead.vue'
import { RouterLink } from 'vue-router'

defineProps({
  section: Object,
})
</script>

<template>
  <section id="services" class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="services-grid">
      <div v-for="category in section.categories" :key="category.code" class="service-card">
        <span class="code">{{ category.code }}</span>
        <h3>{{ category.title }}</h3>
        <ul>
          <li v-for="item in category.items" :key="item">{{ item }}</li>
        </ul>
        <RouterLink class="more" to="/catalogue">View all →</RouterLink>
      </div>
    </div>
    <div v-if="section.disclaimer" class="market-flow-note">{{ section.disclaimer }}</div>
  </section>
</template>
