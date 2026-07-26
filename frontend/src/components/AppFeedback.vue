<script setup>
import { onBeforeUnmount, onMounted } from 'vue'
import { useFeedback } from '../composables/useFeedback'

const { state, dismissToast, resolveConfirm } = useFeedback()

function onKey(e) {
  if (!state.confirm) return
  if (e.key === 'Escape') resolveConfirm(false)
  if (e.key === 'Enter') resolveConfirm(true)
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <Teleport to="body">
    <div class="fb-toast-stack" aria-live="polite" aria-relevant="additions">
      <TransitionGroup name="fb-toast">
        <div
          v-for="t in state.toasts"
          :key="t.id"
          class="fb-toast"
          :class="`fb-toast--${t.type}`"
          role="status"
        >
          <span class="fb-toast-ico" aria-hidden="true">
            <template v-if="t.type === 'success'">✓</template>
            <template v-else-if="t.type === 'error'">!</template>
            <template v-else-if="t.type === 'warn'">⚠</template>
            <template v-else>i</template>
          </span>
          <div class="fb-toast-body">
            <strong>{{ t.title }}</strong>
            <p v-if="t.message">{{ t.message }}</p>
          </div>
          <button type="button" class="fb-toast-close" aria-label="Dismiss" @click="dismissToast(t.id)">×</button>
        </div>
      </TransitionGroup>
    </div>

    <Transition name="fb-modal">
      <div v-if="state.confirm" class="fb-modal-root" role="dialog" aria-modal="true">
        <div class="fb-modal-backdrop" @click="resolveConfirm(false)" />
        <div class="fb-modal-card">
          <div class="fb-modal-ico" :class="{ danger: state.confirm.danger }" aria-hidden="true">
            {{ state.confirm.danger ? '!' : '?' }}
          </div>
          <h3>{{ state.confirm.title }}</h3>
          <p v-if="state.confirm.message">{{ state.confirm.message }}</p>
          <div class="fb-modal-actions">
            <button type="button" class="fb-btn fb-btn-ghost" @click="resolveConfirm(false)">
              {{ state.confirm.cancelLabel }}
            </button>
            <button
              type="button"
              class="fb-btn"
              :class="state.confirm.danger ? 'fb-btn-danger' : 'fb-btn-primary'"
              @click="resolveConfirm(true)"
            >
              {{ state.confirm.confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
