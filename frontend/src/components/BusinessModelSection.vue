<script setup>
import { computed } from 'vue'
import SectionHead from './SectionHead.vue'

const props = defineProps({
  section: Object,
})

const donutGradient = computed(() => {
  let current = 0
  const stops = props.section.streams.map((stream) => {
    const start = current
    current += stream.percent
    return `${stream.color} ${start}% ${current}%`
  })
  return `conic-gradient(${stops.join(', ')})`
})
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="biz">
      <div class="donut-wrap">
        <div class="donut" :style="{ background: donutGradient }">
          <div class="donut-center">
            <div class="n">{{ section.streams_count }}</div>
            <div class="l">Revenue Streams</div>
          </div>
        </div>
      </div>
      <div>
        <div class="rev-legend">
          <div v-for="stream in section.streams" :key="stream.label" class="rev-row">
            <span class="rev-swatch" :style="{ background: stream.color }"></span>
            {{ stream.label }}
            <span class="pct">{{ stream.percent }}%</span>
          </div>
        </div>
        <div class="biz-note">{{ section.note }}</div>
      </div>
    </div>
  </section>
</template>
