<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section id="company" class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="why-grid">
      <div v-for="item in section.why_items" :key="item" class="why-item">
        <div class="hexmark"></div>
        <span class="lbl">{{ item }}</span>
      </div>
    </div>
  </section>
</template>
