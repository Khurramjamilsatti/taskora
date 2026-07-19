<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})

function stars(count) {
  return '★'.repeat(count) + (count < 5 ? '☆'.repeat(5 - count) : '')
}
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" />
    <div class="review-panel">
      <div class="review-score">
        <div class="big">{{ section.score }}</div>
        <div class="stars">★★★★★</div>
        <div class="count">{{ section.count }}</div>
      </div>
      <div class="review-bars">
        <div v-for="row in section.distribution" :key="row.stars" class="review-bar-row">
          <span class="stars-n">{{ row.stars }} star</span>
          <div class="review-bar-track">
            <div class="review-bar-fill" :style="{ width: `${row.percent}%` }"></div>
          </div>
          <span class="pct">{{ row.percent }}%</span>
        </div>
      </div>
    </div>
    <div class="testimonials">
      <div v-for="item in section.testimonials" :key="item.author" class="testimonial">
        <div class="stars">{{ stars(item.stars) }}</div>
        <p>"{{ item.text }}"</p>
        <div class="who">{{ item.author }}</div>
      </div>
    </div>
    <div class="testimonials-note">{{ section.note }}</div>
  </section>
</template>
