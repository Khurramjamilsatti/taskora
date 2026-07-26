<script setup>
defineProps({
  num: String,
  title: String,
  description: String,
  active: { type: String, default: '' },
})

const tabs = [
  { id: 'booking', label: '1. Book a Service', to: '/forms/booking' },
  { id: 'company', label: '3. Company', to: '/forms/company' },
  { id: 'feedback', label: '4. Feedback', to: '/forms/feedback' },
  { id: 'complaint', label: '5. Complaint', to: '/forms/complaint' },
  { id: 'refund', label: '6. Refund', to: '/forms/refund' },
  { id: 'insurance', label: '7. Insurance', to: '/forms/insurance' },
]
</script>

<template>
  <div class="form-page">
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
        <p>Book a service, onboard a company, or manage feedback and claims through the same verified process. Provider signup lives on its own page.</p>
        <div class="form-tabs">
          <RouterLink
            v-for="tab in tabs"
            :key="tab.id"
            :to="tab.to"
            :class="{ active: active === tab.id }"
          >
            {{ tab.label }}
          </RouterLink>
          <RouterLink to="/register/provider">2. Provider Signup</RouterLink>
        </div>
      </div>
    </header>

    <div class="form-area wrap">
      <div class="panel-head">
        <span class="num">{{ num }}</span>
        <h2>{{ title }}</h2>
        <p>{{ description }}</p>
      </div>
      <slot />
    </div>
  </div>
</template>
