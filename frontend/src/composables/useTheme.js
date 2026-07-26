import { onMounted, ref, watch } from 'vue'

const THEME_KEY = 'taskora-dash-theme'
const theme = ref('light')

function applyTheme(value) {
  theme.value = value === 'dark' ? 'dark' : 'light'
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem(THEME_KEY, theme.value)
  }
}

export function useTheme() {
  onMounted(() => {
    const saved = localStorage.getItem(THEME_KEY)
    if (saved === 'dark' || saved === 'light') {
      theme.value = saved
    }
  })

  watch(theme, (value) => {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(THEME_KEY, value)
    }
  })

  function toggleTheme() {
    applyTheme(theme.value === 'light' ? 'dark' : 'light')
  }

  function setTheme(value) {
    applyTheme(value)
  }

  return {
    theme,
    toggleTheme,
    setTheme,
  }
}
