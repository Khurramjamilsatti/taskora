<script setup>
defineProps({
  tag: String,
  title: String,
  description: String,
  dark: Boolean,
})
</script>

<template>
  <div class="section-head">
    <div>
      <div v-if="tag" class="tag">{{ tag }}</div>
      <h2 v-html="title?.replace(/\n/g, '<br>')"></h2>
    </div>
    <p v-if="description">{{ description }}</p>
  </div>
</template>
