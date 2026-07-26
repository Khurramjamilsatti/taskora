<script setup>
import { computed } from 'vue'
import { useAuth } from '../../stores/auth'
import { bookableCategories } from '../../data/bookableServices'

const { state } = useAuth()
const firstName = computed(() => state.user?.name?.split(' ')[0] || 'there')
const serviceCount = computed(() =>
  bookableCategories.reduce((sum, cat) => sum + cat.services.length, 0),
)
</script>

<template>
  <div>
    <div class="db-hero-strip">
      <div>
        <div class="eyebrow">Customer booking</div>
        <h2>Welcome, {{ firstName }}.</h2>
        <p>Browse services, place a booking, and track every request from My Bookings.</p>
      </div>
      <div class="hero-pills">
        <RouterLink class="db-btn db-btn-gold" to="/dashboard/customer/services">Book a Service</RouterLink>
        <RouterLink class="db-btn db-btn-ghost" to="/dashboard/customer/bookings" style="border-color: rgba(255,255,255,0.35); color: #fff;">
          My Bookings
        </RouterLink>
      </div>
    </div>

    <div class="db-stats">
      <div class="db-stat">
        <div class="label">Categories</div>
        <div class="value accent">{{ bookableCategories.length }}</div>
        <div class="hint">Service groups available</div>
      </div>
      <div class="db-stat">
        <div class="label">Services</div>
        <div class="value">{{ serviceCount }}+</div>
        <div class="hint">Ready to book</div>
      </div>
      <div class="db-stat">
        <div class="label">Support</div>
        <div class="value gold">24/7</div>
        <div class="hint">Feedback & claims</div>
      </div>
      <div class="db-stat">
        <div class="label">Account</div>
        <div class="value" style="font-size: 18px;">Active</div>
        <div class="hint">{{ state.user?.email }}</div>
      </div>
    </div>

    <div class="db-panel">
      <div class="db-panel-head">
        <div>
          <h2>How booking works</h2>
          <p>From request to completed job</p>
        </div>
      </div>
      <div class="db-panel-body">
        <div class="db-flow wrap">
          <div class="db-flow-step">
            <div class="bubble">1</div>
            <div class="txt">Book a service</div>
            <div class="arrow">→</div>
          </div>
          <div class="db-flow-step">
            <div class="bubble">2</div>
            <div class="txt">Provider accepts</div>
            <div class="arrow">→</div>
          </div>
          <div class="db-flow-step">
            <div class="bubble">3</div>
            <div class="txt">Agree on price</div>
            <div class="arrow">→</div>
          </div>
          <div class="db-flow-step">
            <div class="bubble">4</div>
            <div class="txt">Accept deal & chat</div>
            <div class="arrow">→</div>
          </div>
          <div class="db-flow-step">
            <div class="bubble">5</div>
            <div class="txt">Provider starts</div>
            <div class="arrow">→</div>
          </div>
          <div class="db-flow-step">
            <div class="bubble">6</div>
            <div class="txt">You mark completed</div>
          </div>
        </div>
        <div class="db-action-grid" style="margin-top: 18px;">
          <RouterLink class="db-action" to="/dashboard/customer/services">
            <strong>Browse all services</strong>
            <span>Pick from 16 categories and book instantly</span>
          </RouterLink>
          <RouterLink class="db-action" to="/dashboard/customer/bookings">
            <strong>View booking list</strong>
            <span>Filter and manage your requests</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
