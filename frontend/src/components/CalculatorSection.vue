<script setup>
import { ref, watch, onMounted } from 'vue'
import SectionHead from './SectionHead.vue'
import { calculateEstimate } from '../api/client'

const props = defineProps({
  section: Object,
})

const selectedService = ref(props.section.service_types[0].base_price)
const size = ref(props.section.size.default)
const frequencyFactor = ref(props.section.frequencies[0].factor)
const amount = ref('PKR —')
const loading = ref(false)

async function updateEstimate() {
  loading.value = true
  try {
    const result = await calculateEstimate({
      base_price: selectedService.value,
      size: size.value,
      frequency_factor: frequencyFactor.value,
    })
    amount.value = result.formatted
  } catch {
    amount.value = 'PKR —'
  } finally {
    loading.value = false
  }
}

watch([selectedService, size, frequencyFactor], updateEstimate)
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
              :value="service.base_price"
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
          <select v-model="frequencyFactor">
            <option
              v-for="freq in section.frequencies"
              :key="freq.id"
              :value="freq.factor"
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
