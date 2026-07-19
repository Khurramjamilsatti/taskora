<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <div class="band">
    <div class="wrap">
      <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
      <div class="tag-cloud">
        <span v-for="tag in section.tags" :key="tag" class="tag-pill">{{ tag }}</span>
      </div>
    </div>
  </div>
</template>
