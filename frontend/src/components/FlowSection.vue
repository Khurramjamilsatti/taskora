<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="flow">
      <div v-for="(step, index) in section.steps" :key="step" class="flow-step">
        <span class="fn">{{ String(index + 1).padStart(2, '0') }}</span>{{ step }}
      </div>
    </div>
  </section>
</template>
