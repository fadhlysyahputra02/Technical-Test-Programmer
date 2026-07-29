<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold select-none border',
      styleMap[statusKey]?.classes || 'bg-gray-500/10 text-gray-400 border-gray-500/20'
    ]"
  >
    <!-- Dot indicator -->
    <span
      :class="[
        'w-1.5 h-1.5 rounded-full mr-1.5',
        styleMap[statusKey]?.dot || 'bg-gray-400'
      ]"
    ></span>
    {{ styleMap[statusKey]?.label || status }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

// Normalize status key (handles both string and Enum value if cast)
const statusKey = computed(() => {
  if (typeof props.status === 'object' && props.status !== null) {
    return props.status.value || String(props.status)
  }
  return String(props.status).toLowerCase()
})

const styleMap = {
  draft: {
    label: 'Draft',
    classes: 'bg-white/[0.04] text-gray-300 border-white/[0.08]',
    dot: 'bg-gray-400'
  },
  submitted: {
    label: 'Diajukan',
    classes: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    dot: 'bg-blue-400'
  },
  under_review: {
    label: 'Sedang Ditinjau',
    classes: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    dot: 'bg-amber-400'
  },
  revision_requested: {
    label: 'Butuh Revisi',
    classes: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    dot: 'bg-yellow-400'
  },
  approved: {
    label: 'Disetujui',
    classes: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-[0_0_12px_rgba(16,185,129,0.05)]',
    dot: 'bg-emerald-400'
  },
  rejected: {
    label: 'Ditolak',
    classes: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    dot: 'bg-rose-400'
  }
}
</script>
