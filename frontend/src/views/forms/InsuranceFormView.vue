<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const form = reactive({
  policy_number: '',
  booking_number: '',
  name: '',
  incident_date: '',
  description: '',
  claim_amount: '',
})
const reference = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('insurance', { ...form })
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
    num="Form 07"
    title="Insurance Claim Form"
    description="For covered incidents under a Taskora protection plan or partner insurance policy."
    active="insurance"
  >
    <div v-if="reference" class="confirm-banner show"><span class="tick">✓</span>Claim submitted. Reference: {{ reference }}</div>
    <p v-if="error" class="auth-alert">{{ error }}</p>
    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="grid2">
          <div class="field"><label>Policy Number</label><input v-model="form.policy_number" required /></div>
          <div class="field"><label>Booking Number</label><input v-model="form.booking_number" /></div>
          <div class="field"><label>Your Name</label><input v-model="form.name" required /></div>
          <div class="field"><label>Incident Date</label><input v-model="form.incident_date" type="date" /></div>
          <div class="field"><label>Claim Amount (PKR)</label><input v-model="form.claim_amount" type="number" /></div>
          <div class="field full"><label>Incident Description</label><textarea v-model="form.description" required /></div>
        </div>
      </div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Insurance Claim' }}</button>
      </div>
    </form>
  </FormPageShell>
</template>
