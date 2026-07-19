<script setup>
import SectionHead from './SectionHead.vue'

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
        <span class="more">View all →</span>
      </div>
    </div>
  </section>
</template>
