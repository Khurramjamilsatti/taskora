import { ref, onMounted } from 'vue'
import { fetchSiteData } from '../api/client'

const siteData = ref(null)
const loading = ref(true)
const error = ref(null)

let loadPromise = null

export function useSiteData() {
  async function load() {
    if (siteData.value) {
      return siteData.value
    }

    if (!loadPromise) {
      loadPromise = fetchSiteData()
        .then((data) => {
          siteData.value = data
          error.value = null
          return data
        })
        .catch((err) => {
          error.value = err.message
          throw err
        })
        .finally(() => {
          loading.value = false
        })
    }

    return loadPromise
  }

  onMounted(load)

  return { siteData, loading, error, load }
}
