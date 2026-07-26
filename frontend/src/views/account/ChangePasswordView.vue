<script setup>
import { reactive, ref } from 'vue'
import { apiChangePassword, ApiError } from '../../api/client'

const saving = ref(false)
const message = ref('')
const error = ref('')
const errors = ref({})

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

async function save() {
  saving.value = true
  message.value = ''
  error.value = ''
  errors.value = {}
  try {
    const res = await apiChangePassword({ ...form })
    message.value = res.message || 'Password changed.'
    form.current_password = ''
    form.password = ''
    form.password_confirmation = ''
  } catch (err) {
    if (err instanceof ApiError) {
      error.value = err.message
      errors.value = err.errors || {}
    } else {
      error.value = 'Failed to change password'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="db-panel">
    <div class="db-panel-head">
      <div>
        <h2>Change password</h2>
        <p>Use a strong password of at least 8 characters</p>
      </div>
    </div>
    <div class="db-panel-body">
      <p v-if="message" class="bk-success">{{ message }}</p>
      <p v-if="error" class="auth-alert">{{ error }}</p>

      <form class="bk-form" @submit.prevent="save">
        <div class="bk-section">
          <div class="bk-grid">
            <label class="full">
              Current password
              <input v-model="form.current_password" type="password" required autocomplete="current-password" />
              <small v-if="errors.current_password">{{ errors.current_password[0] }}</small>
            </label>
            <label>
              New password
              <input v-model="form.password" type="password" required minlength="8" autocomplete="new-password" />
              <small v-if="errors.password">{{ errors.password[0] }}</small>
            </label>
            <label>
              Confirm new password
              <input v-model="form.password_confirmation" type="password" required minlength="8" autocomplete="new-password" />
            </label>
          </div>
        </div>
        <div class="bk-actions">
          <button type="submit" class="db-btn db-btn-gold" :disabled="saving">
            {{ saving ? 'Updating…' : 'Update password' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
