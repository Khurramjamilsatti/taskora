<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="route">
      <div v-for="(step, index) in section.steps" :key="step.title" class="route-step">
        <div class="node">{{ index + 1 }}</div>
        <h4>{{ step.title }}</h4>
        <p>{{ step.description }}</p>
      </div>
    </div>
    <div class="career-strip">
      <div
        v-for="(level, index) in section.levels"
        :key="level"
        class="career-chip"
        :class="{ 'career-chip--highlight': index === section.levels.length - 1 }"
      >
        {{ level }}
      </div>
    </div>
  </section>
</template>

<style scoped>
.career-chip--highlight {
  background: var(--black);
  color: var(--gold);
}
</style>
