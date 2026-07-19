<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="dash">
      <div class="dash-side">
        <div class="u">{{ section.user.name }}</div>
        <div class="e">{{ section.user.email }}</div>
        <div
          v-for="(item, index) in section.nav"
          :key="item"
          class="dash-nav-item"
          :class="{ active: index === 0 }"
        >
          <span v-if="index === 0" class="dot"></span>{{ item }}
        </div>
      </div>
      <div class="dash-main">
        <div class="dash-cards">
          <div v-for="card in section.cards" :key="card.label" class="dash-card">
            <div class="n">{{ card.value }}</div>
            <div class="l">{{ card.label }}</div>
          </div>
        </div>
        <div v-for="booking in section.bookings" :key="booking.who" class="dash-booking">
          <div>
            <div class="who">{{ booking.who }}</div>
            <div class="what">{{ booking.what }}</div>
          </div>
          <div class="dash-status">{{ booking.status }}</div>
        </div>
        <div class="dash-rate">
          <div>
            <div class="who" style="font-size: 13.5px">{{ section.rate_prompt.title }}</div>
            <div class="what" style="font-size: 11.5px">{{ section.rate_prompt.subtitle }}</div>
          </div>
          <div class="stars">★★★★★</div>
        </div>
      </div>
    </div>
  </section>
</template>
