<script setup>
defineProps({
  section: Object,
})
</script>

<template>
  <section class="wrap">
    <div class="trust">
      <div>
        <div class="tag">{{ section.tag }}</div>
        <h2 style="margin-bottom: 18px">{{ section.title }}</h2>
        <p style="color: rgba(18, 32, 26, 0.62); font-size: 14.5px; max-width: 440px">
          {{ section.description }}
        </p>
        <div class="trust-badges">
          <span v-for="badge in section.badges" :key="badge" class="trust-badge">{{ badge }}</span>
        </div>
      </div>
      <div class="trust-visual">
        <div v-for="check in section.checks" :key="check" class="trust-row">
          <span>{{ check }}</span>
          <span class="check">✓</span>
        </div>
      </div>
    </div>
  </section>
</template>
