<script setup>
defineProps({
  footer: Object,
})

function isRoute(href) {
  return href === '#services' || href?.startsWith('/')
}

function linkTo(href) {
  if (href === '#services') return '/catalogue'
  return href
}
</script>

<template>
  <footer>
    <div class="wrap">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="footer-logo">
            <img src="/taskora-icon.png" alt="Taskora" />
            <span class="word">TASKORA</span>
          </div>
          <p>{{ footer.description }}</p>
        </div>
        <div v-for="column in footer.columns" :key="column.title" class="footer-col">
          <h5>{{ column.title }}</h5>
          <ul>
            <li v-for="link in column.links" :key="link.label">
              <RouterLink v-if="isRoute(link.href)" :to="linkTo(link.href)">{{ link.label }}</RouterLink>
              <a v-else :href="link.href">{{ link.label }}</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>{{ footer.copyright }}</span>
        <span>{{ footer.legal_links }}</span>
      </div>
    </div>
  </footer>
</template>
