<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-purple-400 to-indigo-300 bg-clip-text text-transparent">Daftar Permohonan</h1>
          <p class="text-sm text-gray-400 mt-1">Lacak dan kelola status permohonan Anda</p>
        </div>
        <router-link
          to="/applications/create"
          class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-all shadow-lg shadow-purple-600/10 active:scale-[0.98]"
        >
          Buat Permohonan Baru
        </router-link>
      </div>

      <!-- Filters Section -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-white/[0.02] border border-white/[0.08] p-5 rounded-xl">
        <BaseSelect
          label="Pilih Proyek"
          v-model="filters.project_id"
          :options="projectOptions"
          placeholder="Semua Proyek"
          @update:modelValue="handleFilterChange"
        />

        <BaseSelect
          label="Pilih Status"
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Semua Status"
          @update:modelValue="handleFilterChange"
        />

        <BaseInput
          label="Tanggal Mulai"
          v-model="filters.date_from"
          type="date"
          @input="handleFilterChange"
        />

        <BaseInput
          label="Tanggal Selesai"
          v-model="filters.date_to"
          type="date"
          @input="handleFilterChange"
        />
      </div>

      <!-- Data Table -->
      <DataTable
        :columns="columns"
        :rows="applicationStore.list"
        :loading="applicationStore.loading"
      >
        <template #application_number="{ row }">
          <router-link :to="`/applications/${row.id}`" class="text-purple-400 hover:underline font-semibold font-mono">
            {{ row.application_number }}
          </router-link>
        </template>
        <template #project="{ row }">
          {{ row.project?.name || '–' }}
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #submitted_at="{ row }">
          {{ formatDate(row.submitted_at) }}
        </template>
        <template #action="{ row }">
          <div class="flex items-center gap-2">
            <router-link
              :to="`/applications/${row.id}`"
              class="text-xs bg-white/5 border border-white/10 hover:bg-white/10 text-white font-medium px-3 py-1.5 rounded-lg transition-all"
            >
              Detail
            </router-link>
            <router-link
              v-if="row.status === 'draft' || row.status === 'revision_requested'"
              :to="`/applications/${row.id}/edit`"
              class="text-xs bg-purple-500/10 border border-purple-500/20 hover:bg-purple-500/20 text-purple-300 font-medium px-3 py-1.5 rounded-lg transition-all"
            >
              Edit
            </router-link>
          </div>
        </template>
      </DataTable>

      <!-- Pagination -->
      <Pagination
        v-if="applicationStore.pagination.last_page > 1"
        :current-page="applicationStore.pagination.current_page"
        :last-page="applicationStore.pagination.last_page"
        :total="applicationStore.pagination.total"
        :per-page="applicationStore.pagination.per_page"
        @change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApplicationStore } from '../../stores/application'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import DataTable from '../../components/DataTable.vue'
import Pagination from '../../components/Pagination.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const applicationStore = useApplicationStore()
const { error: toastError } = useToast()

const projectOptions = ref([])

const filters = ref({
  project_id: '',
  status: '',
  date_from: '',
  date_to: '',
  page: 1
})

const columns = [
  { key: 'application_number', label: 'Nomor Permohonan' },
  { key: 'project', label: 'Proyek' },
  { key: 'status', label: 'Status' },
  { key: 'submitted_at', label: 'Tanggal Diajukan' },
  { key: 'action', label: 'Aksi' }
]

const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'submitted', label: 'Diajukan' },
  { value: 'under_review', label: 'Sedang Ditinjau' },
  { value: 'revision_requested', label: 'Butuh Revisi' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'rejected', label: 'Ditolak' }
]

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchProjects = async () => {
  try {
    const response = await api.get('/api/projects', { params: { limit: 100 } })
    projectOptions.value = (response.data.data || []).map((p) => ({
      value: p.id,
      label: p.name
    }))
  } catch (err) {
    console.error('Gagal mengambil daftar proyek filter:', err)
  }
}

const fetchApplications = () => {
  applicationStore.fetchApplications(filters.value).catch(() => {
    toastError('Gagal mengambil daftar permohonan.')
  })
}

const handleFilterChange = () => {
  filters.value.page = 1
  fetchApplications()
}

const handlePageChange = (page) => {
  filters.value.page = page
  fetchApplications()
}

onMounted(() => {
  fetchProjects()
  fetchApplications()
})
</script>
