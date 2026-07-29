<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border border-white/[0.08] rounded-xl bg-white/[0.01]">
    <!-- Items range info -->
    <div class="text-xs text-gray-400">
      Menampilkan
      <span class="font-semibold text-white">{{ rangeStart }}</span>
      sampai
      <span class="font-semibold text-white">{{ rangeEnd }}</span>
      dari
      <span class="font-semibold text-white">{{ total }}</span>
      item
    </div>

    <!-- Page Controls -->
    <div class="flex items-center gap-1">
      <!-- Prev Button -->
      <button
        :disabled="currentPage === 1"
        class="p-2 border border-white/[0.08] rounded-lg bg-white/[0.02] text-gray-400 hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors"
        @click="goToPage(currentPage - 1)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Numeric Page Buttons -->
      <button
        v-for="page in visiblePages"
        :key="page"
        :disabled="page === '...'"
        :class="[
          'px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors border',
          page === currentPage
            ? 'bg-purple-600 border-purple-500 text-white'
            : 'bg-white/[0.02] border-white/[0.08] text-gray-400 hover:text-white hover:bg-white/[0.05] disabled:border-transparent disabled:bg-transparent'
        ]"
        @click="goToPage(page)"
      >
        {{ page }}
      </button>

      <!-- Next Button -->
      <button
        :disabled="currentPage === lastPage"
        class="p-2 border border-white/[0.08] rounded-lg bg-white/[0.02] text-gray-400 hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors"
        @click="goToPage(currentPage + 1)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  lastPage: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 20
  }
})

const emit = defineEmits(['change'])

const rangeStart = computed(() => {
  if (props.total === 0) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const rangeEnd = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.total)
})

const visiblePages = computed(() => {
  const current = props.currentPage
  const last = props.lastPage
  const delta = 2
  const pages = []

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }

  return pages
})

const goToPage = (page) => {
  if (page === '...' || page === props.currentPage) return
  if (page >= 1 && page <= props.lastPage) {
    emit('change', page)
  }
}
</script>
