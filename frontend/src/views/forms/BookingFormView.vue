<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const cities = ['Islamabad', 'Rawalpindi', 'Lahore', 'Karachi', 'Faisalabad', 'Peshawar', 'Multan']
const categories = [
  'Home Services', 'Construction & Renovation', 'Architects & Interior Design', 'Legal Services',
  'Chartered Accountants & Taxation', 'IT Services & Software', 'Tutors & Education',
  'Corporate & Business Services', 'Facility Management', 'HR & Recruitment',
  'Digital Marketing & Media', 'Health & Wellness', 'Event Management',
  'Logistics & Transport', 'Security Services', 'Insurance, Financial & Investment',
]

const form = reactive({
  cnic: '',
  name: '',
  mobile: '',
  email: '',
  city: cities[0],
  address: '',
  map_link: '',
  category: categories[0],
  service: '',
  description: '',
  urgency: 'Normal',
  preferred_date: '',
  budget: '',
  quotation: 'Yes',
  inspection: 'No',
  payment: 'Cash',
  signature: '',
})

const reference = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('booking', { ...form })
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
    num="Form 01"
    title="Customer Service Booking Form"
    description="Tell us what you need and where — we'll match you with a verified professional."
    active="booking"
  >
    <div v-if="reference" class="confirm-banner show">
      <span class="tick">✓</span>
      Booking request captured. Reference: {{ reference }}
    </div>
    <p v-if="error" class="auth-alert">{{ error }}</p>

    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="fieldset-title">Customer Information</div>
        <div class="grid2">
          <div class="field"><label>Full Name</label><input v-model="form.name" required /></div>
          <div class="field"><label>Mobile Number</label><input v-model="form.mobile" type="tel" required /></div>
          <div class="field"><label>Email Address</label><input v-model="form.email" type="email" /></div>
          <div class="field">
            <label>City</label>
            <select v-model="form.city"><option v-for="c in cities" :key="c">{{ c }}</option></select>
          </div>
          <div class="field full"><label>Complete Address</label><input v-model="form.address" required /></div>
          <div class="field full"><label>Google Map Location</label><input v-model="form.map_link" placeholder="Pin or paste map link" /></div>
          <div class="field"><label>CNIC <span class="opt">(optional)</span></label><input v-model="form.cnic" placeholder="XXXXX-XXXXXXX-X" /></div>
        </div>
      </div>

      <div class="fieldset">
        <div class="fieldset-title">Service Details</div>
        <div class="grid2">
          <div class="field">
            <label>Main Category</label>
            <select v-model="form.category"><option v-for="c in categories" :key="c">{{ c }}</option></select>
          </div>
          <div class="field"><label>Service Required</label><input v-model="form.service" placeholder="e.g. Electrician visit" required /></div>
          <div class="field full"><label>Description of Work</label><textarea v-model="form.description" /></div>
          <div class="field">
            <label>Urgency Level</label>
            <div class="radio-row">
              <label v-for="u in ['Normal', 'Urgent', 'Emergency']" :key="u">
                <input v-model="form.urgency" type="radio" :value="u" /> {{ u }}
              </label>
            </div>
          </div>
          <div class="field"><label>Preferred Date</label><input v-model="form.preferred_date" type="date" /></div>
        </div>
      </div>

      <div class="fieldset">
        <div class="fieldset-title">Pricing & Payment</div>
        <div class="grid2">
          <div class="field"><label>Estimated Budget (PKR)</label><input v-model="form.budget" type="number" /></div>
          <div class="field">
            <label>Quotation Required?</label>
            <div class="radio-row">
              <label><input v-model="form.quotation" type="radio" value="Yes" />Yes</label>
              <label><input v-model="form.quotation" type="radio" value="No" />No</label>
            </div>
          </div>
          <div class="field full">
            <label>Payment Method</label>
            <div class="chip-toggle-row">
              <label
                v-for="p in ['Cash', 'Bank Transfer', 'Card', 'Wallet', 'Easypaisa', 'JazzCash']"
                :key="p"
                class="chip-toggle"
                :class="{ checked: form.payment === p }"
              >
                <input v-model="form.payment" type="radio" :value="p" />{{ p }}
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="declaration">
        <input type="checkbox" required />
        <span>I confirm that all information provided is accurate and agree to Taskora Terms & Conditions.</span>
      </div>
      <div class="field"><label>Customer Signature (type full name)</label><input v-model="form.signature" required /></div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Booking Request' }}</button>
        <span class="form-note">Assigned within 15 minutes during service hours</span>
      </div>
    </form>
  </FormPageShell>
</template>
