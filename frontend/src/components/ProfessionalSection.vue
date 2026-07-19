<script setup>
defineProps({
  section: Object,
})
</script>

<template>
  <section id="professional" class="wrap">
    <div class="pro-split">
      <div class="pro-copy">
        <div class="tag">{{ section.tag }}</div>
        <h2>{{ section.title }}</h2>
        <p>{{ section.description }}</p>
        <a :href="section.cta.href" class="btn btn-gold">{{ section.cta.label }}</a>
      </div>
      <div class="pro-benefits">
        <div v-for="(benefit, index) in section.benefits" :key="benefit" class="pro-benefit">
          <span class="n">{{ String(index + 1).padStart(2, '0') }}</span>
          <span class="t">{{ benefit }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
