<script setup>
import { reactive, ref } from 'vue'
import FormPageShell from '../../components/FormPageShell.vue'
import { apiSubmitForm, ApiError } from '../../api/client'

const ratings = reactive({
  professionalism: 0,
  quality: 0,
  communication: 0,
  punctuality: 0,
  cleanliness: 0,
  behaviour: 0,
  pricing: 0,
  overall: 0,
})
const form = reactive({ comments: '', recommend: 'Yes' })
const reference = ref('')
const error = ref('')
const loading = ref(false)

function setRating(key, value) {
  ratings[key] = value
}

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const res = await apiSubmitForm('feedback', { ...form, ratings: { ...ratings } })
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
    num="Form 04"
    title="Customer Feedback Form"
    description="Rate your completed service — this feeds directly into your professional's performance score."
    active="feedback"
  >
    <div v-if="reference" class="confirm-banner show"><span class="tick">✓</span>Thank you — feedback recorded. Reference: {{ reference }}</div>
    <p v-if="error" class="auth-alert">{{ error }}</p>
    <form @submit.prevent="submit">
      <div class="fieldset">
        <div class="fieldset-title">Rate Your Experience (1–5)</div>
        <div class="grid2">
          <div v-for="(value, key) in ratings" :key="key" class="field">
            <label>{{ key }}</label>
            <div class="star-row">
              <button
                v-for="n in 5"
                :key="n"
                type="button"
                :class="{ on: n <= value }"
                @click="setRating(key, n)"
              >★</button>
            </div>
          </div>
        </div>
      </div>
      <div class="fieldset">
        <div class="field full"><label>Comments</label><textarea v-model="form.comments" /></div>
        <div class="field">
          <label>Would you recommend this professional?</label>
          <div class="radio-row">
            <label><input v-model="form.recommend" type="radio" value="Yes" />Yes</label>
            <label><input v-model="form.recommend" type="radio" value="No" />No</label>
          </div>
        </div>
      </div>
      <div class="submit-row">
        <button class="btn btn-fill" type="submit" :disabled="loading">{{ loading ? 'Submitting…' : 'Submit Feedback' }}</button>
      </div>
    </form>
  </FormPageShell>
</template>
