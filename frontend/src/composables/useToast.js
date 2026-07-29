import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  const add = (message, type = 'success', duration = 3000) => {
    const id = Date.now() + Math.random()
    toasts.value.push({ id, message, type })
    
    setTimeout(() => {
      remove(id)
    }, duration)
  }

  const success = (message, duration) => add(message, 'success', duration)
  const error = (message, duration) => add(message, 'error', duration)
  const info = (message, duration) => add(message, 'info', duration)
  const warning = (message, duration) => add(message, 'warning', duration)

  const remove = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return {
    toasts,
    add,
    success,
    error,
    info,
    warning,
    remove
  }
}
