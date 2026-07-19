<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  nav: Object,
})
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
        <a v-for="link in nav.links" :key="link.href" :href="link.href">{{ link.label }}</a>
      </div>
      <div class="nav-actions">
        <RouterLink to="/login" class="btn btn-ghost btn-mini">Sign in</RouterLink>
        <a :href="nav.cta.href" class="btn btn-gold btn-mini">{{ nav.cta.label }}</a>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.nav-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
</style>
