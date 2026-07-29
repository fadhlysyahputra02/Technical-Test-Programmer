import { ref, computed } from 'vue'

export function usePagination(defaultLimit = 20) {
  const page = ref(1)
  const limit = ref(defaultLimit)
  const total = ref(0)
  const lastPage = ref(1)

  const hasNext = computed(() => page.value < lastPage.value)
  const hasPrev = computed(() => page.value > 1)

  const nextPage = () => {
    if (hasNext.value) {
      page.value++
    }
  }

  const prevPage = () => {
    if (hasPrev.value) {
      page.value--
    }
  }

  const setPage = (p) => {
    const val = parseInt(p)
    if (val >= 1 && val <= lastPage.value) {
      page.value = val
    }
  }

  const updateMeta = (meta) => {
    if (!meta) return
    page.value = meta.current_page || page.value
    limit.value = meta.per_page || limit.value
    total.value = meta.total || 0
    lastPage.value = meta.last_page || 1
  }

  const reset = () => {
    page.value = 1
    total.value = 0
    lastPage.value = 1
  }

  return {
    page,
    limit,
    total,
    lastPage,
    hasNext,
    hasPrev,
    nextPage,
    prevPage,
    setPage,
    updateMeta,
    reset
  }
}
