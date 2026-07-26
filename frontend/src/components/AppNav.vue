<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  nav: Object,
})

function linkTo(href) {
  if (href === '#services') return '/catalogue'
  return href
}

function isRoute(href) {
  return href === '#services' || href?.startsWith('/')
}
</script>

<template>
  <nav>
    <div class="nav-inner">
      <div class="logo">
        <img src="/taskora-icon.png" alt="Taskora icon" />
        <div>
          <div class="word">TASKORA</div>
          <div class="powered-by">Powered by Trovec Technologies</div>
        </div>
      </div>
      <div class="nav-links">
        <template v-for="link in nav.links" :key="link.href">
          <RouterLink v-if="isRoute(link.href)" :to="linkTo(link.href)">{{ link.label }}</RouterLink>
          <a v-else :href="link.href">{{ link.label }}</a>
        </template>
      </div>
      <div class="nav-actions">
        <RouterLink to="/login" class="auth-link">Sign in / Join</RouterLink>
        <a :href="nav.cta.href" class="btn btn-gold btn-mini">{{ nav.cta.label }}</a>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.nav-actions {
  display: flex;
  align-items: center;
  gap: 18px;
  flex-shrink: 0;
}

.auth-link {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--ink);
  text-decoration: none;
  white-space: nowrap;
}

.auth-link:hover {
  color: var(--green);
}

.nav-actions .btn-mini {
  white-space: nowrap;
}
</style>
