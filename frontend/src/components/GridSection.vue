<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
  type: {
    type: String,
    default: 'GUIDE',
  },
})
</script>

<template>
  <section :id="type === 'GUIDE' ? 'knowledge' : undefined" class="wrap">
    <SectionHead v-if="section.tag" :tag="section.tag" :title="section.title" :description="section.description" />
    <SectionHead v-else :title="section.title" />
    <div class="kn-grid">
      <div v-for="item in section.items || section.guides" :key="item" class="kn-card">
        <span class="fn">{{ type }}</span>
        <span class="t">{{ item }}</span>
      </div>
    </div>
  </section>
</template>
