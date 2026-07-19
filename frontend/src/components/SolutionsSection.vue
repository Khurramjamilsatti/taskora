<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section id="business" class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="solutions">
      <div v-for="item in section.items" :key="item.title" class="solution">
        <div class="tag">{{ item.tag }}</div>
        <h3>{{ item.title }}</h3>
        <p>{{ item.description }}</p>
        <ul>
          <li v-for="feature in item.features" :key="feature">{{ feature }}</li>
        </ul>
      </div>
    </div>
  </section>
</template>
