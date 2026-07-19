<script setup>
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
      <a :href="nav.cta.href" class="btn btn-gold btn-mini">{{ nav.cta.label }}</a>
    </div>
  </nav>
</template>
