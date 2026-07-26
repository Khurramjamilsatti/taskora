<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchCatalogue } from '../api/client'

const data = ref(null)
const loading = ref(true)
const error = ref('')
const query = ref('')

onMounted(async () => {
  try {
    data.value = await fetchCatalogue()
  } catch (err) {
    error.value = err.message || 'Failed to load catalogue'
  } finally {
    loading.value = false
  }
})

const colorMap = {
  green: 'var(--green)',
  'green-dark': 'var(--green-dark)',
  black: 'var(--black)',
  gold: 'var(--gold)',
}

function fieldTone(field) {
  return field.tone || ''
}

function focusSearch() {
  document.getElementById('catSearch')?.focus()
}

const filteredCategories = computed(() => {
  if (!data.value?.categories) return []
  const q = query.value.trim().toLowerCase()
  if (!q) return data.value.categories

  return data.value.categories
    .map((cat) => {
      if (cat.name.toLowerCase().includes(q)) return { ...cat, open: true }
      if (cat.chips?.some((c) => c.toLowerCase().includes(q))) {
        return {
          ...cat,
          chips: cat.chips.filter((c) => c.toLowerCase().includes(q)),
          open: true,
        }
      }
      if (cat.subcategories) {
        const subs = cat.subcategories
          .map((sub) => ({
            ...sub,
            chips: sub.chips.filter(
              (c) => c.toLowerCase().includes(q) || sub.title.toLowerCase().includes(q),
            ),
          }))
          .filter((sub) => sub.chips.length || sub.title.toLowerCase().includes(q))
        if (subs.length) return { ...cat, subcategories: subs, open: true }
      }
      return null
    })
    .filter(Boolean)
})
</script>

<template>
  <div class="catalogue-page">
    <nav class="cat-nav">
      <div class="nav-inner">
        <RouterLink to="/" class="logo">
          <img src="/taskora-icon.png" alt="Taskora" />
          <span class="word">TASKORA</span>
        </RouterLink>
        <RouterLink to="/" class="back-link">← Back to Site</RouterLink>
      </div>
    </nav>

    <div v-if="loading" class="loading-state">Loading catalogue…</div>
    <div v-else-if="error" class="loading-state">{{ error }}</div>

    <template v-else-if="data">
      <header class="ph">
        <div class="wrap ph-inner">
          <div class="tag">{{ data.meta.tag }}</div>
          <h1>{{ data.meta.title }}</h1>
          <p>{{ data.meta.description }}</p>
          <div class="search-bar">
            <input
              id="catSearch"
              v-model="query"
              type="text"
              placeholder="Search a profession — e.g. Electrician, Tax Consultant, UI/UX Designer…"
            />
            <button type="button" class="btn" @click="focusSearch">Search Catalogue</button>
          </div>
          <div class="ph-stats">
            <div v-for="stat in data.stats" :key="stat.label" class="ph-stat">
              <div class="n">{{ stat.value }}</div>
              <div class="l">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </header>

      <section class="wrap">
        <div class="section-head">
          <div class="tag">{{ data.verification.tag }}</div>
          <h2>{{ data.verification.title }}</h2>
          <p>{{ data.verification.description }}</p>
        </div>
        <div class="verify-grid">
          <div v-for="check in data.verification.checks" :key="check" class="verify-item">
            <span class="ck">✓</span>
            <span>{{ check }}</span>
          </div>
        </div>
      </section>

      <section class="wrap" style="padding-top: 0;">
        <div class="section-head">
          <div class="tag">{{ data.browse.tag }}</div>
          <h2>{{ data.browse.title }}</h2>
          <p>{{ data.browse.description }}</p>
        </div>

        <div class="cat-list">
          <details
            v-for="cat in filteredCategories"
            :key="`${cat.name}-${query}`"
            class="cat-accordion"
            :open="Boolean(cat.open)"
          >
            <summary>
              <span class="summary-left">
                <span
                  class="cat-hex"
                  :style="{ background: colorMap[cat.color] || 'var(--green)' }"
                ></span>
                {{ cat.name }}
                <span class="cat-count">{{ cat.count }}</span>
              </span>
              <span class="chevron">+</span>
            </summary>
            <div class="cat-body">
              <template v-if="cat.subcategories?.length">
                <div v-for="sub in cat.subcategories" :key="sub.title" class="subcat">
                  <div class="subcat-title">{{ sub.title }}</div>
                  <div class="chip-row">
                    <span v-for="chip in sub.chips" :key="chip" class="chip">{{ chip }}</span>
                  </div>
                </div>
              </template>
              <div v-else class="subcat flat">
                <div class="chip-row">
                  <span v-for="chip in cat.chips" :key="chip" class="chip">{{ chip }}</span>
                </div>
                <div v-if="cat.note" class="cat-note">{{ cat.note }}</div>
              </div>
            </div>
          </details>
        </div>
      </section>

      <section class="wrap" style="padding-top: 0;">
        <div class="section-head">
          <div class="tag">{{ data.profile.tag }}</div>
          <h2>{{ data.profile.title }}</h2>
          <p>{{ data.profile.description }}</p>
        </div>
        <div class="profile-mock">
          <div class="profile-side">
            <div class="profile-avatar">{{ data.profile.initials }}</div>
            <div class="nm">{{ data.profile.name }}</div>
            <div class="role">{{ data.profile.role }}</div>
            <div class="profile-badge">✓ Verified Pro</div>
          </div>
          <div class="profile-main">
            <div class="profile-grid">
              <div v-for="field in data.profile.fields" :key="field.label" class="pf-item">
                <div class="l">{{ field.label }}</div>
                <div class="v" :class="fieldTone(field)">{{ field.value }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="wrap" style="padding-top: 0;">
        <div class="section-head">
          <div class="tag">{{ data.quality.tag }}</div>
          <h2>{{ data.quality.title }}</h2>
        </div>
        <div class="qp-grid">
          <div v-for="item in data.quality.items" :key="item" class="qp-item">
            <span class="dot"></span>{{ item }}
          </div>
        </div>
      </section>

      <div class="cta-strip">
        <h2>{{ data.cta.title }}</h2>
        <div class="btns">
          <RouterLink :to="data.cta.primary.href" class="btn btn-fill">
            {{ data.cta.primary.label }}
          </RouterLink>
          <RouterLink :to="data.cta.secondary.href" class="btn btn-ghost">
            {{ data.cta.secondary.label }}
          </RouterLink>
        </div>
      </div>

      <footer class="cat-footer">
        © 2026 Taskora (Pvt.) Ltd. · A Trovec Technologies Company — Master Professional Catalogue v1.0
      </footer>
    </template>
  </div>
</template>
