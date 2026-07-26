<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { useTheme } from '../composables/useTheme'

const props = defineProps({
  role: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  navSections: { type: Array, default: () => [] },
  section: { type: String, default: 'overview' },
})

const emit = defineEmits(['update:section'])

const router = useRouter()
const { state, logout } = useAuth()
const { theme, toggleTheme } = useTheme()
const sidebarOpen = ref(false)

const initials = computed(() => {
  const name = state.user?.name || 'U'
  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase() || '')
    .join('')
})

const roleLabel = computed(() => (props.role === 'provider' ? 'Provider' : 'Customer'))

function selectSection(id) {
  emit('update:section', id)
  sidebarOpen.value = false
}

async function handleLogout() {
  await logout()
  router.push('/login')
}
</script>

<template>
  <div class="db-shell" :class="{ 'sidebar-open': sidebarOpen }" :data-theme="theme">
    <div class="db-overlay" @click="sidebarOpen = false" />

    <aside class="db-sidebar">
      <RouterLink to="/" class="db-sidebar-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <div>
          <div class="word">TASKORA</div>
          <div class="sub">Workspace</div>
        </div>
      </RouterLink>

      <div class="db-role-badge">{{ roleLabel }} account</div>

      <nav class="db-nav">
        <template v-for="(group, gi) in navSections" :key="gi">
          <div v-if="group.label" class="db-nav-label">{{ group.label }}</div>
          <template v-for="item in group.items" :key="item.id || item.href">
            <button
              v-if="item.id"
              type="button"
              class="db-nav-item"
              :class="{ active: section === item.id }"
              @click="selectSection(item.id)"
            >
              <span class="ico">{{ item.icon }}</span>
              {{ item.label }}
            </button>
            <RouterLink
              v-else
              :to="item.href"
              class="db-nav-item"
              @click="sidebarOpen = false"
            >
              <span class="ico">{{ item.icon }}</span>
              {{ item.label }}
            </RouterLink>
          </template>
        </template>
      </nav>

      <div class="db-sidebar-foot">
        <div class="db-user">
          <div class="db-avatar">{{ initials }}</div>
          <div>
            <div class="name">{{ state.user?.name }}</div>
            <div class="email">{{ state.user?.email }}</div>
          </div>
        </div>
        <button type="button" class="db-theme-toggle" @click="toggleTheme">
          <span>{{ theme === 'light' ? 'Light theme' : 'Dark theme' }}</span>
          <span>{{ theme === 'light' ? '☀' : '☾' }}</span>
        </button>
        <button type="button" class="db-logout" @click="handleLogout">
          <span>Log out</span>
          <span>→</span>
        </button>
      </div>
    </aside>

    <div class="db-main">
      <header class="db-topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
          <button type="button" class="db-menu-btn" aria-label="Open menu" @click="sidebarOpen = true">
            ☰
          </button>
          <div>
            <h1>{{ title }}</h1>
            <p v-if="subtitle">{{ subtitle }}</p>
          </div>
        </div>
        <div class="db-topbar-actions">
          <slot name="actions" />
        </div>
      </header>
      <div class="db-content">
        <slot />
      </div>
    </div>
  </div>
</template>
