<template>
  <div class="w-full overflow-hidden border border-white/[0.08] rounded-xl bg-white/[0.01] backdrop-blur-md">
    <div class="w-full overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <!-- Table Header -->
        <thead>
          <tr class="border-b border-white/[0.08] bg-white/[0.02]">
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-400 select-none"
              :class="[col.sortable ? 'cursor-pointer hover:text-white transition-colors' : '']"
              @click="handleSort(col)"
            >
              <div class="flex items-center gap-1.5">
                {{ col.label }}
                <!-- Sort Icons -->
                <span v-if="col.sortable" class="text-gray-500">
                  <svg
                    v-if="sortKey !== col.key"
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                  </svg>
                  <svg
                    v-else-if="sortDirection === 'asc'"
                    class="w-3.5 h-3.5 text-purple-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg
                    v-else
                    class="w-3.5 h-3.5 text-purple-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <!-- Loading State Skeletons -->
          <template v-if="loading">
            <tr
              v-for="i in 5"
              :key="'skeleton-' + i"
              class="border-b border-white/[0.04] animate-pulse"
            >
              <td
                v-for="col in columns"
                :key="'skeleton-td-' + col.key"
                class="px-6 py-4"
              >
                <div class="h-4 bg-white/10 rounded w-2/3"></div>
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else-if="rows.length === 0">
            <td :colspan="columns.length" class="px-6 py-12 text-center text-gray-500 text-sm">
              <svg
                class="mx-auto h-12 w-12 text-gray-600 mb-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4" />
              </svg>
              Tidak ada data tersedia.
            </td>
          </tr>

          <!-- Rows list -->
          <tr
            v-else
            v-for="(row, idx) in rows"
            :key="row.id || idx"
            class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-6 py-4 text-sm text-gray-300"
            >
              <slot :name="col.key" :row="row" :value="row[col.key]">
                {{ row[col.key] ?? '–' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  rows: {
    type: Array,
    required: true,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['sort'])

const sortKey = ref('')
const sortDirection = ref('asc') // 'asc' or 'desc'

const handleSort = (col) => {
  if (!col.sortable) return

  if (sortKey.value === col.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = col.key
    sortDirection.value = 'asc'
  }

  emit('sort', {
    key: sortKey.value,
    direction: sortDirection.value
  })
}
</script>
