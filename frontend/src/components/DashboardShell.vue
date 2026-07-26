<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { useTheme } from '../composables/useTheme'
import {
  apiNotifications,
  apiMarkNotificationRead,
  apiMarkAllNotificationsRead,
} from '../api/client'

const props = defineProps({
  role: { type: String, required: true },
  navItems: { type: Array, default: () => [] },
})

const route = useRoute()
const router = useRouter()
const { state, logout } = useAuth()
const { theme, setTheme } = useTheme()
const sidebarOpen = ref(false)
const themeOpen = ref(false)
const profileOpen = ref(false)
const notifOpen = ref(false)
const topbarRef = ref(null)
const notifications = ref([])
const unread = ref(0)
let notifTimer = null

const settingsBase = computed(() =>
  props.role === 'provider' ? '/dashboard/provider/settings' : '/dashboard/customer/settings',
)

const initials = computed(() => {
  const name = state.user?.name || 'U'
  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase() || '')
    .join('')
})

const roleLabel = computed(() => (props.role === 'provider' ? 'Provider' : 'Customer'))
const firstName = computed(() => state.user?.name?.split(' ')[0] || 'Account')
const title = computed(() => {
  const hit = [...route.matched].reverse().find((r) => r.meta?.title)
  return hit?.meta.title || `${roleLabel.value} workspace`
})
const subtitle = computed(() => {
  const hit = [...route.matched].reverse().find((r) => r.meta?.subtitle)
  return hit?.meta.subtitle || ''
})

function isActive(item) {
  if (item.match) {
    return route.path === item.match || route.path.startsWith(`${item.match}/`)
  }
  if (item.to === '/dashboard/customer' || item.to === '/dashboard/provider') {
    return route.path === item.to
  }
  return route.path === item.to || route.path.startsWith(`${item.to}/`) || route.name === item.name
}

function closeMenus() {
  themeOpen.value = false
  profileOpen.value = false
  notifOpen.value = false
}

function toggleThemeMenu() {
  profileOpen.value = false
  notifOpen.value = false
  themeOpen.value = !themeOpen.value
}

function toggleProfileMenu() {
  themeOpen.value = false
  notifOpen.value = false
  profileOpen.value = !profileOpen.value
}

async function toggleNotifMenu() {
  themeOpen.value = false
  profileOpen.value = false
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) await loadNotifications()
}

function chooseTheme(value) {
  setTheme(value)
  themeOpen.value = false
}

async function loadNotifications() {
  try {
    const res = await apiNotifications()
    notifications.value = res.data || []
    unread.value = res.unread || 0
  } catch {
    // ignore
  }
}

async function openNotification(item) {
  try {
    if (!item.read_at) {
      await apiMarkNotificationRead(item.id)
      item.read_at = new Date().toISOString()
      unread.value = Math.max(0, unread.value - 1)
    }
  } catch {
    // ignore
  }
  notifOpen.value = false
  if (item.link) router.push(item.link)
}

async function markAllRead() {
  try {
    await apiMarkAllNotificationsRead()
    notifications.value = notifications.value.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() }))
    unread.value = 0
  } catch {
    // ignore
  }
}

async function handleLogout() {
  closeMenus()
  await logout()
  router.push('/login')
}

function onDocClick(event) {
  if (!topbarRef.value?.contains(event.target)) closeMenus()
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  loadNotifications()
  notifTimer = setInterval(loadNotifications, 20000)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  if (notifTimer) clearInterval(notifTimer)
})
</script>

<template>
  <div class="db-shell" :class="{ 'sidebar-open': sidebarOpen }" :data-theme="theme">
    <div class="db-overlay" @click="sidebarOpen = false" />

    <aside class="db-sidebar">
      <RouterLink to="/" class="db-sidebar-brand" @click="sidebarOpen = false">
        <img src="/taskora-icon.png" alt="Taskora" />
        <div>
          <div class="word">TASKORA</div>
          <div class="sub">{{ roleLabel }} booking</div>
        </div>
      </RouterLink>

      <div class="db-sidebar-scroll">
        <template v-for="(group, gi) in navItems" :key="gi">
          <div v-if="group.label" class="db-nav-label">{{ group.label }}</div>
          <div class="db-nav-list">
            <RouterLink
              v-for="item in group.items"
              :key="item.to"
              :to="item.to"
              class="db-nav-item"
              :class="{ active: isActive(item) }"
              @click="sidebarOpen = false"
            >
              <span class="ico">{{ item.icon }}</span>
              <span class="label">{{ item.label }}</span>
            </RouterLink>
          </div>
        </template>
      </div>
    </aside>

    <div class="db-main">
      <header ref="topbarRef" class="db-topbar">
        <div class="db-topbar-left">
          <button type="button" class="db-menu-btn" aria-label="Open menu" @click="sidebarOpen = true">☰</button>
          <div>
            <h1>{{ title }}</h1>
            <p v-if="subtitle">{{ subtitle }}</p>
          </div>
        </div>

        <div class="db-topbar-right">
          <div class="db-topbar-actions">
            <slot name="actions" />
          </div>

          <div class="db-dd" :class="{ open: notifOpen }">
            <button
              type="button"
              class="db-dd-trigger db-notif-trigger"
              aria-label="Notifications"
              @click.stop="toggleNotifMenu"
            >
              <span class="db-dd-ico">🔔</span>
              <span v-if="unread" class="db-notif-badge">{{ unread > 9 ? '9+' : unread }}</span>
            </button>
            <div v-if="notifOpen" class="db-dd-menu db-notif-menu">
              <div class="db-notif-head">
                <strong>Notifications</strong>
                <button v-if="unread" type="button" class="linkish" @click="markAllRead">Mark all read</button>
              </div>
              <div v-if="!notifications.length" class="db-empty" style="padding: 16px;">No notifications yet.</div>
              <button
                v-for="item in notifications"
                :key="item.id"
                type="button"
                class="db-notif-item"
                :class="{ unread: !item.read_at }"
                @click="openNotification(item)"
              >
                <strong>{{ item.title }}</strong>
                <span>{{ item.body }}</span>
                <small>{{ new Date(item.created_at).toLocaleString() }}</small>
              </button>
            </div>
          </div>

          <div class="db-dd" :class="{ open: themeOpen }">
            <button type="button" class="db-dd-trigger db-theme-trigger" @click.stop="toggleThemeMenu">
              <span class="db-dd-ico">{{ theme === 'dark' ? '☾' : '☀' }}</span>
              <span class="db-dd-text">{{ theme === 'dark' ? 'Dark' : 'Light' }}</span>
              <span class="db-caret">▾</span>
            </button>
            <div v-if="themeOpen" class="db-dd-menu">
              <button type="button" class="db-dd-option" :class="{ active: theme === 'light' }" @click="chooseTheme('light')">
                <span>☀ Light</span><span v-if="theme === 'light'" class="check">✓</span>
              </button>
              <button type="button" class="db-dd-option" :class="{ active: theme === 'dark' }" @click="chooseTheme('dark')">
                <span>☾ Dark</span><span v-if="theme === 'dark'" class="check">✓</span>
              </button>
            </div>
          </div>

          <div class="db-dd" :class="{ open: profileOpen }">
            <button type="button" class="db-dd-trigger db-profile-trigger" @click.stop="toggleProfileMenu">
              <span class="db-avatar sm">{{ initials }}</span>
              <span class="db-profile-meta">
                <span class="nm">{{ firstName }}</span>
                <span class="rl">{{ roleLabel }}</span>
              </span>
              <span class="db-caret">▾</span>
            </button>
            <div v-if="profileOpen" class="db-dd-menu db-profile-menu">
              <div class="db-dd-head">
                <div class="db-avatar">{{ initials }}</div>
                <div>
                  <div class="nm">{{ state.user?.name }}</div>
                  <div class="em">{{ state.user?.email }}</div>
                </div>
              </div>
              <div class="db-dd-divider" />
              <RouterLink :to="`${settingsBase}/profile`" class="db-dd-option" @click="closeMenus">
                Profile settings
              </RouterLink>
              <RouterLink :to="`${settingsBase}/password`" class="db-dd-option" @click="closeMenus">
                Change password
              </RouterLink>
              <div class="db-dd-divider" />
              <button type="button" class="db-dd-option danger" @click="handleLogout">Log out</button>
            </div>
          </div>
        </div>
      </header>

      <div class="db-content">
        <slot />
      </div>
    </div>
  </div>
</template>
