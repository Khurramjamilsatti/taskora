<script setup>
defineProps({
  section: Object,
})
</script>

<template>
  <div class="vision">
    <div class="wrap">
      <div class="tag">{{ section.tag }}</div>
      <h2>{{ section.title }}</h2>
      <p>{{ section.subtitle }}</p>
      <div class="legal">{{ section.legal }}</div>
    </div>
  </div>
</template>
