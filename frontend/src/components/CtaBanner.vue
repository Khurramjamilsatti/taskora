<script setup>
defineProps({
  section: Object,
})
</script>

<template>
  <div id="download" class="cta-banner">
    <div class="tag">{{ section.tag }}</div>
    <h2 v-html="section.title.replace(/\n/g, '<br>')"></h2>
    <div class="btns">
      <a
        v-for="button in section.buttons"
        :key="button.label"
        :href="button.href"
        class="btn"
        :class="button.variant === 'fill' ? 'btn-fill' : 'btn-ghost'"
      >
        {{ button.label }}
      </a>
    </div>
  </div>
</template>
