<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  num: String,
  title: String,
  description: String,
  active: { type: String, default: '' },
})

const route = useRoute()
const embedded = computed(() => route.path.startsWith('/dashboard/'))
</script>

<template>
  <div class="form-page" :class="{ 'form-page--embedded': embedded }">
    <template v-if="!embedded">
      <nav class="form-nav">
        <div class="nav-inner">
          <RouterLink to="/" class="logo">
            <img src="/taskora-icon.png" alt="Taskora" />
            <span class="word">TASKORA</span>
          </RouterLink>
          <RouterLink to="/" class="back-link">← Back to Site</RouterLink>
        </div>
      </nav>

      <header class="fh">
        <div class="wrap">
          <div class="tag">Forms & Registration</div>
          <h1>Get set up with Taskora.</h1>
          <p>Book a service, onboard a company, or manage feedback and claims through the same verified process.</p>
        </div>
      </header>
    </template>

    <div class="form-area" :class="{ wrap: !embedded }">
      <div class="panel-head">
        <span class="num">{{ num }}</span>
        <h2>{{ title }}</h2>
        <p>{{ description }}</p>
      </div>
      <slot />
    </div>
  </div>
</template>
