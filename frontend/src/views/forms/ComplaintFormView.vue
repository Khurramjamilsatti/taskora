<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const form = reactive({
  booking_number: '',
  name: '',
  provider: '',
  type: 'Service Quality',
  resolution: 'Re-service',
  description: '',
})
const reference = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('complaint', { ...form })
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
    num="Form 05"
    title="Complaint Form"
    description="Something didn't go as expected? Let us know and we'll investigate."
    active="complaint"
  >
    <div v-if="reference" class="confirm-banner show"><span class="tick">✓</span>Complaint logged. Reference: {{ reference }}</div>
    <p v-if="error" class="auth-alert">{{ error }}</p>
    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="grid2">
          <div class="field"><label>Booking Number</label><input v-model="form.booking_number" required /></div>
          <div class="field"><label>Your Name</label><input v-model="form.name" required /></div>
          <div class="field"><label>Professional / Provider</label><input v-model="form.provider" /></div>
          <div class="field">
            <label>Complaint Type</label>
            <select v-model="form.type">
              <option>Service Quality</option><option>Behaviour</option><option>Pricing Dispute</option>
              <option>Property Damage</option><option>No-Show</option><option>Other</option>
            </select>
          </div>
          <div class="field">
            <label>Resolution Required</label>
            <select v-model="form.resolution">
              <option>Re-service</option><option>Partial Refund</option><option>Full Refund</option>
              <option>Apology / Warning</option><option>Other</option>
            </select>
          </div>
          <div class="field full"><label>Description</label><textarea v-model="form.description" required /></div>
        </div>
      </div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Complaint' }}</button>
        <span class="form-note">Target resolution: within 24 hours</span>
      </div>
    </form>
  </FormPageShell>
</template>
