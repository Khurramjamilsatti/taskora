<script setup>
defineProps({
  stats: Array,
})
</script>

<template>
  <div class="stats-bar">
    <div class="stats-grid">
      <div v-for="stat in stats" :key="stat.label" class="stat">
        <div class="num">{{ stat.value }}</div>
        <div class="lbl">{{ stat.label }}</div>
      </div>
    </div>
  </div>
</template>
