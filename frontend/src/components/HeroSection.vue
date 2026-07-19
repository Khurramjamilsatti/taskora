<script setup>
defineProps({
  hero: Object,
})
</script>

<template>
  <header class="hero">
    <div class="hero-grid">
      <div>
        <div class="tag">{{ hero.tag }}</div>
        <h1>
          {{ hero.title[0] }}<br />
          {{ hero.title[1] }}<br />
          <em>{{ hero.title_emphasis }}</em>
        </h1>
        <p class="lede">{{ hero.lede }}</p>
        <div class="hero-ctas">
          <a
            v-for="cta in hero.ctas"
            :key="cta.label"
            :href="cta.href"
            class="btn"
            :class="cta.variant === 'gold' ? 'btn-gold' : 'btn-ghost'"
          >
            {{ cta.label }}
          </a>
        </div>
        <div class="hero-mini-stats">
          <div v-for="stat in hero.stats" :key="stat.label">
            <div class="n">{{ stat.value }}</div>
            <div class="l">{{ stat.label }}</div>
          </div>
        </div>
      </div>
      <div class="phone">
        <div class="phone-screen">
          <div class="phone-top">
            <img src="/taskora-icon.png" alt="" />
            <span>TASKORA</span>
          </div>
          <div class="phone-search">🔍 Search services…</div>
          <div class="phone-cat-grid">
            <div v-for="cat in hero.phone.categories" :key="cat" class="phone-cat">
              <div class="ic"></div>
              <div class="t">{{ cat }}</div>
            </div>
          </div>
          <div class="phone-list-h">
            <span>Popular Services</span>
            <span>View All</span>
          </div>
          <div v-for="service in hero.phone.popular_services" :key="service.name" class="phone-row">
            <span class="l">{{ service.name }}</span>
            <span class="r">★ {{ service.rating }}</span>
          </div>
          <div class="phone-nav">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="ticker" style="margin-top: 56px">
      <div class="ticker-track">
        <span v-for="(item, i) in [...hero.ticker, ...hero.ticker]" :key="`${item}-${i}`">{{ item }}</span>
      </div>
    </div>
  </header>
</template>
