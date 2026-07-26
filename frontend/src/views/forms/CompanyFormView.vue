<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const form = reactive({
  company_name: '',
  secp: '',
  ntn: '',
  strn: '',
  address: '',
  website: '',
  employees: '',
  contact_name: '',
  mobile: '',
  email: '',
  services: '',
  coverage: '',
})
const reference = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('company', { ...form })
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
    num="Form 03"
    title="Company Registration Form"
    description="Onboard your business for corporate contracts, AMC coverage, or enterprise solutions."
    active="company"
  >
    <div v-if="reference" class="confirm-banner show"><span class="tick">✓</span>Company profile submitted. Reference: {{ reference }}</div>
    <p v-if="error" class="auth-alert">{{ error }}</p>
    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="fieldset-title">Company Details</div>
        <div class="grid2">
          <div class="field"><label>Company Name</label><input v-model="form.company_name" required /></div>
          <div class="field"><label>SECP Registration Number</label><input v-model="form.secp" /></div>
          <div class="field"><label>NTN</label><input v-model="form.ntn" /></div>
          <div class="field"><label>STRN</label><input v-model="form.strn" /></div>
          <div class="field full"><label>Business Address</label><input v-model="form.address" /></div>
          <div class="field"><label>Website</label><input v-model="form.website" placeholder="https://" /></div>
          <div class="field"><label>Number of Employees</label><input v-model="form.employees" type="number" /></div>
        </div>
      </div>
      <div class="fieldset">
        <div class="fieldset-title">Contact Person</div>
        <div class="grid3">
          <div class="field"><label>Contact Name</label><input v-model="form.contact_name" required /></div>
          <div class="field"><label>Mobile</label><input v-model="form.mobile" type="tel" required /></div>
          <div class="field"><label>Email</label><input v-model="form.email" type="email" /></div>
        </div>
      </div>
      <div class="fieldset">
        <div class="fieldset-title">Services & Coverage</div>
        <div class="field full"><label>Services Required</label><textarea v-model="form.services" /></div>
        <div class="field full"><label>Coverage Areas</label><input v-model="form.coverage" /></div>
      </div>
      <div class="declaration"><input type="checkbox" required /><span>I confirm authority to register this company and agree to the Taskora Corporate Service Agreement.</span></div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Company Profile' }}</button>
        <span class="form-note">Corporate accounts are reviewed within 2 business days</span>
      </div>
    </form>
  </FormPageShell>
</template>
