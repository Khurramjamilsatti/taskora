<script setup>
import { reactive, ref, watch } from 'vue'
import { apiSubmitBookingFeedback, ApiError } from '../api/client'
import { toastError, toastSuccess } from '../composables/useFeedback'

const props = defineProps({
  booking: { type: Object, required: true },
})

const emit = defineEmits(['submitted'])

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
const loading = ref(false)
const done = ref(false)

watch(
  () => props.booking?.feedback,
  (fb) => {
    done.value = Boolean(fb)
  },
  { immediate: true },
)

function setRating(key, value) {
  ratings[key] = value
}

const labels = {
  professionalism: 'Professionalism',
  quality: 'Quality',
  communication: 'Communication',
  punctuality: 'Punctuality',
  cleanliness: 'Cleanliness',
  behaviour: 'Behaviour',
  pricing: 'Pricing',
  overall: 'Overall',
}

async function submit() {
  if (!ratings.overall) {
    toastError('Rating required', 'Please set at least an overall star rating.')
    return
  }
  loading.value = true
  try {
    const res = await apiSubmitBookingFeedback(props.booking.id, {
      ratings: { ...ratings },
      comments: form.comments,
      recommend: form.recommend,
    })
    done.value = true
    toastSuccess('Thank you', 'Your feedback was submitted.')
    emit('submitted', res.booking)
  } catch (err) {
    toastError('Feedback failed', err instanceof ApiError ? err.message : 'Please try again.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="bk-feedback">
    <div class="bk-feedback-head">
      <h3>Rate this service</h3>
      <p v-if="!done">Job is completed — share feedback for {{ booking.provider?.name || 'your provider' }}.</p>
      <p v-else>Feedback submitted for this booking.</p>
    </div>

    <div v-if="done || booking.feedback" class="bk-feedback-done">
      <strong>✓ Feedback recorded</strong>
      <span v-if="booking.feedback?.overall || ratings.overall">
        Overall {{ booking.feedback?.ratings?.overall || booking.feedback?.overall || ratings.overall }}/5
      </span>
    </div>

    <form v-else class="bk-feedback-form" @submit.prevent="submit">
      <div class="bk-feedback-grid">
        <label v-for="(value, key) in ratings" :key="key">
          {{ labels[key] || key }}
          <div class="star-row">
            <button
              v-for="n in 5"
              :key="n"
              type="button"
              :class="{ on: n <= value }"
              @click="setRating(key, n)"
            >★</button>
          </div>
        </label>
      </div>
      <label class="full">
        Comments
        <textarea v-model="form.comments" rows="3" placeholder="Optional comments" />
      </label>
      <div class="bk-feedback-recommend">
        <span>Would you recommend this professional?</span>
        <label><input v-model="form.recommend" type="radio" value="Yes" /> Yes</label>
        <label><input v-model="form.recommend" type="radio" value="No" /> No</label>
      </div>
      <button type="submit" class="db-btn db-btn-gold" :disabled="loading">
        {{ loading ? 'Submitting…' : 'Submit feedback' }}
      </button>
    </form>
  </div>
</template>
