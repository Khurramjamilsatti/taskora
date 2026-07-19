<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'

const router = useRouter()
const { state, logout } = useAuth()

async function handleLogout() {
  await logout()
  router.push('/login')
}
</script>

<template>
  <div class="dash">
    <header class="dash-nav">
      <RouterLink to="/" class="dash-brand">
        <img src="/taskora-icon.png" alt="Taskora" />
        <span>TASKORA</span>
      </RouterLink>
      <button class="btn btn-ghost btn-mini" @click="handleLogout">Log out</button>
    </header>

    <main class="dash-body">
      <p class="tag">Dashboard</p>
      <h1>Welcome back, {{ state.user?.name }}</h1>
      <p class="dash-sub">You are signed in as {{ state.user?.email }}.</p>

      <div class="dash-grid">
        <div class="dash-card">
          <h3>Account</h3>
          <p>{{ state.user?.name }}</p>
          <p class="muted">{{ state.user?.email }}</p>
        </div>
        <div class="dash-card">
          <h3>Bookings</h3>
          <p class="big">0</p>
          <p class="muted">No bookings yet.</p>
        </div>
        <div class="dash-card">
          <h3>Estimates</h3>
          <p class="big">0</p>
          <p class="muted">Saved estimates will appear here.</p>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dash { min-height: 100vh; background: var(--paper); }
.dash-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 32px;
  background: var(--white);
  border-bottom: 1px solid var(--line);
}
.dash-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  letter-spacing: 0.04em;
}
.dash-brand img { width: 32px; height: 32px; }
.btn-mini { padding: 9px 18px; font-size: 12px; }
.dash-body { max-width: 1000px; margin: 0 auto; padding: 48px 32px; }
.dash-body h1 { font-size: 32px; margin-bottom: 8px; }
.dash-sub { color: rgba(18, 32, 26, 0.65); margin-bottom: 36px; }
.dash-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}
.dash-card {
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 24px;
}
.dash-card h3 { font-size: 15px; margin-bottom: 12px; color: var(--green); }
.dash-card .big { font-size: 34px; font-weight: 700; font-family: 'Space Grotesk', sans-serif; }
.muted { color: rgba(18, 32, 26, 0.55); font-size: 14px; }
</style>
