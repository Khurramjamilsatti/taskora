<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const form = reactive({
  booking_number: '',
  name: '',
  amount: '',
  reason: '',
  method: 'Original payment method',
})
const reference = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('refund', { ...form })
    reference.value = res.reference
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Submission failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <FormPageShell
    num="Form 06"
    title="Refund Request Form"
    description="Request a refund for a booking — we'll review and respond within 3 business days."
    active="refund"
  >
    <div v-if="reference" class="confirm-banner show"><span class="tick">✓</span>Refund request received. Reference: {{ reference }}</div>
    <p v-if="error" class="auth-alert">{{ error }}</p>
    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="grid2">
          <div class="field"><label>Booking Number</label><input v-model="form.booking_number" required /></div>
          <div class="field"><label>Your Name</label><input v-model="form.name" required /></div>
          <div class="field"><label>Refund Amount (PKR)</label><input v-model="form.amount" type="number" required /></div>
          <div class="field"><label>Preferred Method</label><input v-model="form.method" /></div>
          <div class="field full"><label>Reason</label><textarea v-model="form.reason" required /></div>
        </div>
      </div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Refund Request' }}</button>
      </div>
    </form>
  </FormPageShell>
</template>
