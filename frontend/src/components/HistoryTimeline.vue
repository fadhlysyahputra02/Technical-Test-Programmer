<template>
  <div class="relative pl-6 border-l-2 border-white/[0.08] ml-3 space-y-8">
    <div
      v-for="(item, idx) in histories"
      :key="item.id || idx"
      class="relative"
    >
      <!-- Timeline Node Circle -->
      <span class="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#0F0F16] ring-2 ring-purple-500">
        <span class="h-2 w-2 rounded-full bg-purple-400"></span>
      </span>

      <!-- Card / Timeline Item -->
      <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-5 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
          <!-- Status Transition -->
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-400">
              {{ formatStatus(item.from_status) }}
            </span>
            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
            <span :class="['text-xs font-semibold px-2 py-0.5 rounded border', getStatusClasses(item.to_status)]">
              {{ formatStatus(item.to_status) }}
            </span>
          </div>

          <!-- Timestamp -->
          <span class="text-xs text-gray-500">{{ formatDate(item.created_at) }}</span>
        </div>

        <!-- Notes -->
        <p v-if="item.notes" class="text-sm text-gray-300 italic mb-3">
          "{{ item.notes }}"
        </p>

        <!-- Actor info -->
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Oleh: <span class="font-semibold text-white">{{ item.changed_by?.name || 'Sistem' }}</span>
        </div>
      </div>
    </div>

    <!-- Empty History State -->
    <div v-if="histories.length === 0" class="text-center py-6 text-gray-500 text-sm">
      Belum ada riwayat perubahan status.
    </div>
  </div>
</template>

<script setup>
defineProps({
  histories: {
    type: Array,
    required: true,
    default: () => []
  }
})

const formatStatus = (status) => {
  if (!status) return 'Mulai'
  const map = {
    draft: 'Draft',
    submitted: 'Diajukan',
    under_review: 'Sedang Ditinjau',
    revision_requested: 'Butuh Revisi',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return map[status.toLowerCase()] || status
}

const getStatusClasses = (status) => {
  if (!status) return 'bg-gray-500/10 text-gray-400 border-gray-500/20'
  const map = {
    draft: 'bg-white/[0.04] text-gray-300 border-white/[0.08]',
    submitted: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    under_review: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    revision_requested: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    approved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    rejected: 'bg-rose-500/10 text-rose-400 border-rose-500/20'
  }
  return map[status.toLowerCase()] || 'bg-gray-500/10 text-gray-400 border-gray-500/20'
}

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  const date = new Date(dateStr)
  return date.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
