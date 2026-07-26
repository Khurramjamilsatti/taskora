import { reactive } from 'vue'

const state = reactive({
  toasts: [],
  confirm: null,
})

let toastSeq = 0
let confirmResolver = null

function pushToast({ type = 'info', title, message, duration = 4200 } = {}) {
  const id = ++toastSeq
  state.toasts.push({ id, type, title, message })
  if (duration > 0) {
    window.setTimeout(() => dismissToast(id), duration)
  }
  return id
}

export function dismissToast(id) {
  const idx = state.toasts.findIndex((t) => t.id === id)
  if (idx >= 0) state.toasts.splice(idx, 1)
}

export function toastSuccess(title, message = '') {
  return pushToast({ type: 'success', title, message })
}

export function toastError(title, message = '') {
  return pushToast({ type: 'error', title, message, duration: 5600 })
}

export function toastInfo(title, message = '') {
  return pushToast({ type: 'info', title, message })
}

export function toastWarn(title, message = '') {
  return pushToast({ type: 'warn', title, message })
}

/**
 * Elegant confirm dialog. Resolves true/false.
 */
export function confirmAction({
  title = 'Are you sure?',
  message = '',
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  danger = false,
} = {}) {
  return new Promise((resolve) => {
    if (confirmResolver) {
      confirmResolver(false)
      confirmResolver = null
    }
    confirmResolver = resolve
    state.confirm = { title, message, confirmLabel, cancelLabel, danger }
  })
}

export function resolveConfirm(result) {
  if (confirmResolver) {
    confirmResolver(Boolean(result))
    confirmResolver = null
  }
  state.confirm = null
}

export function useFeedback() {
  return {
    state,
    toastSuccess,
    toastError,
    toastInfo,
    toastWarn,
    dismissToast,
    confirmAction,
    resolveConfirm,
  }
}
