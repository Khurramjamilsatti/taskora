<script setup>
import { ref, watch, onMounted } from 'vue'
import SectionHead from './SectionHead.vue'
import { calculateEstimate } from '../api/client'

const props = defineProps({
  section: Object,
})

const selectedService = ref(props.section.service_types[0]?.id)
const size = ref(props.section.size.default)
const selectedFrequency = ref(props.section.frequencies[0]?.id)
const amount = ref('PKR —')
const loading = ref(false)

async function updateEstimate() {
  if (!selectedService.value || !selectedFrequency.value) {
    return
  }

  loading.value = true
  try {
    const result = await calculateEstimate({
      service_id: selectedService.value,
      frequency_id: selectedFrequency.value,
      size: size.value,
    })
    amount.value = result.formatted
  } catch {
    amount.value = 'PKR —'
  } finally {
    loading.value = false
  }
}

watch([selectedService, size, selectedFrequency], updateEstimate)
onMounted(updateEstimate)
</script>

<template>
  <section class="wrap">
    <SectionHead :tag="section.tag" :title="section.title" :description="section.description" />
    <div class="calc">
      <div class="calc-form">
        <div class="calc-field">
          <label>Service Type</label>
          <select v-model="selectedService">
            <option
              v-for="service in section.service_types"
              :key="service.id"
              :value="service.id"
            >
              {{ service.label }}
            </option>
          </select>
        </div>
        <div class="calc-field">
          <label>Property Size (Marla)</label>
          <input v-model.number="size" type="range" :min="section.size.min" :max="section.size.max" />
          <div class="calc-range-val"><span>{{ size }}</span> {{ section.size.unit }}</div>
        </div>
        <div class="calc-field">
          <label>Frequency</label>
          <select v-model="selectedFrequency">
            <option
              v-for="freq in section.frequencies"
              :key="freq.id"
              :value="freq.id"
            >
              {{ freq.label }}
            </option>
          </select>
        </div>
      </div>
      <div class="calc-result">
        <div class="lbl">Estimated Cost</div>
        <div class="amount">{{ loading ? 'Calculating…' : amount }}</div>
        <div class="note">{{ section.note }}</div>
      </div>
    </div>
  </section>
</template>
