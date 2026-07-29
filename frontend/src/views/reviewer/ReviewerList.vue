<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-purple-400 to-indigo-300 bg-clip-text text-transparent">Antrean Persetujuan</h1>
        <p class="text-sm text-gray-400 mt-1">Tinjau dan berikan penilaian pada permohonan masuk</p>
      </div>

      <!-- Filters Section -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-white/[0.02] border border-white/[0.08] p-5 rounded-xl">
        <BaseInput
          label="Cari Nomor Permohonan"
          v-model="filters.search"
          placeholder="Cari nomor permohonan..."
          @input="handleFilterChange"
        />

        <BaseSelect
          label="Filter Status"
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Semua Status"
          @update:modelValue="handleFilterChange"
        />

        <BaseSelect
          label="Filter Proyek"
          v-model="filters.project_id"
          :options="projectOptions"
          placeholder="Semua Proyek"
          @update:modelValue="handleFilterChange"
        />

        <BaseInput
          label="ID Pemohon (Applicant ID)"
          v-model="filters.applicant_id"
          type="number"
          placeholder="Masukkan ID Pemohon..."
          @input="handleFilterChange"
        />

        <BaseInput
          label="Tanggal Submit (Mulai)"
          v-model="filters.date_from"
          type="date"
          @input="handleFilterChange"
        />

        <BaseInput
          label="Tanggal Submit (Selesai)"
          v-model="filters.date_to"
          type="date"
          @input="handleFilterChange"
        />
      </div>

      <!-- Data Table -->
      <DataTable
        :columns="columns"
        :rows="reviewStore.list"
        :loading="reviewStore.loading"
      >
        <template #application_number="{ row }">
          <router-link :to="`/reviewer/applications/${row.id}`" class="text-purple-400 hover:underline font-semibold font-mono">
            {{ row.application_number }}
          </router-link>
        </template>
        <template #project="{ row }">
          {{ row.project?.name || '–' }}
        </template>
        <template #applicant="{ row }">
          <div class="flex flex-col">
            <span class="font-medium text-white">{{ row.applicant?.name || '–' }}</span>
            <span class="text-xs text-gray-500 font-mono">ID: {{ row.applicant_id }}</span>
          </div>
        </template>
        <template #status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #submitted_at="{ row }">
          {{ formatDateTime(row.submitted_at) }}
        </template>
        <template #action="{ row }">
          <router-link
            :to="`/reviewer/applications/${row.id}`"
            class="inline-block bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all"
          >
            Tinjau
          </router-link>
        </template>
      </DataTable>

      <!-- Pagination -->
      <Pagination
        v-if="reviewStore.pagination.last_page > 1"
        :current-page="reviewStore.pagination.current_page"
        :last-page="reviewStore.pagination.last_page"
        :total="reviewStore.pagination.total"
        :per-page="reviewStore.pagination.per_page"
        @change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useReviewStore } from '../../stores/review'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import DataTable from '../../components/DataTable.vue'
import Pagination from '../../components/Pagination.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const reviewStore = useReviewStore()
const { error: toastError } = useToast()

const projectOptions = ref([])

const filters = ref({
  search: '',
  status: '',
  project_id: '',
  applicant_id: '',
  date_from: '',
  date_to: '',
  page: 1
})

const columns = [
  { key: 'application_number', label: 'Nomor Permohonan' },
  { key: 'project', label: 'Proyek' },
  { key: 'applicant', label: 'Pemohon' },
  { key: 'status', label: 'Status' },
  { key: 'submitted_at', label: 'Tanggal Submit' },
  { key: 'action', label: 'Aksi' }
]

const statusOptions = [
  { value: 'submitted', label: 'Diajukan' },
  { value: 'under_review', label: 'Sedang Ditinjau' },
  { value: 'revision_requested', label: 'Butuh Revisi' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'rejected', label: 'Ditolak' }
]

const formatDateTime = (dateStr) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchFilterProjects = async () => {
  try {
    const response = await api.get('/api/projects', { params: { limit: 100 } })
    projectOptions.value = (response.data.data || []).map((p) => ({
      value: p.id,
      label: p.name
    }))
  } catch (err) {
    console.error('Gagal mengambil daftar proyek filter reviewer:', err)
  }
}

const fetchIncomingApplications = () => {
  reviewStore.fetchApplicationsForReview(filters.value).catch(() => {
    toastError('Gagal mengambil daftar permohonan masuk.')
  })
}

const handleFilterChange = () => {
  filters.value.page = 1
  fetchIncomingApplications()
}

const handlePageChange = (page) => {
  filters.value.page = page
  fetchIncomingApplications()
}

onMounted(() => {
  fetchFilterProjects()
  fetchIncomingApplications()
})
</script>
