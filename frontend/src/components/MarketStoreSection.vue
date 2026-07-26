<script setup>
import SectionHead from './SectionHead.vue'

defineProps({
  section: Object,
})
</script>

<template>
  <section id="market-store" class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />

    <div class="market-cat-grid">
      <div v-for="category in section.categories" :key="category" class="market-cat">
        <div class="hex"></div>
        <div class="t">{{ category }}</div>
      </div>
    </div>

    <div class="market-block">
      <SectionHead
        :tag="section.pricing.tag"
        :title="section.pricing.title"
        description=""
      />
      <div class="price-table-wrap">
        <table class="price-table">
          <thead>
            <tr>
              <th v-for="header in section.pricing.headers" :key="header">{{ header }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in section.pricing.rows" :key="row.product">
              <td>{{ row.product }}</td>
              <td>{{ row.market }}</td>
              <td>{{ row.wholesale }}</td>
              <td>{{ row.partner }}</td>
              <td class="save">{{ row.save }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="market-flow-note">{{ section.pricing.note }}</div>
    </div>

    <div class="market-block">
      <SectionHead
        :tag="section.how_it_works.tag"
        :title="section.how_it_works.title"
        description=""
      />
      <div class="route market-route">
        <div
          v-for="(step, index) in section.how_it_works.steps"
          :key="step.title"
          class="route-step"
        >
          <div class="node">{{ index + 1 }}</div>
          <h4>{{ step.title }}</h4>
          <p>{{ step.description }}</p>
        </div>
      </div>
    </div>

    <div class="solutions market-audiences">
      <div v-for="audience in section.audiences" :key="audience.title" class="solution">
        <div class="tag">{{ audience.tag }}</div>
        <h3>{{ audience.title }}</h3>
        <p>{{ audience.description }}</p>
        <ul>
          <li v-for="item in audience.items" :key="item">{{ item }}</li>
        </ul>
      </div>
    </div>
  </section>
</template>

<style scoped>
.market-block {
  margin-top: 44px;
}

.market-block :deep(.section-head) {
  margin-bottom: 22px;
}

.market-block :deep(.section-head h2) {
  font-size: 24px;
}

.market-audiences {
  margin-top: 44px;
}
</style>
