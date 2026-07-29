<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-purple-400 to-indigo-300 bg-clip-text text-transparent">Daftar Proyek</h1>
          <p class="text-sm text-gray-400 mt-1">Kelola proyek pengajuan permohonan Anda</p>
        </div>
        <router-link
          to="/projects/create"
          class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-all shadow-lg shadow-purple-600/10 active:scale-[0.98]"
        >
          Buat Proyek Baru
        </router-link>
      </div>

      <!-- Filters Section -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-white/[0.02] border border-white/[0.08] p-5 rounded-xl">
        <BaseInput
          label="Cari Nama Proyek"
          v-model="filters.search"
          placeholder="Cari berdasarkan nama..."
          @input="handleFilterChange"
        />

        <BaseSelect
          label="Status Proyek"
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Semua Status"
          @update:modelValue="handleFilterChange"
        />
      </div>

      <!-- Data Table -->
      <DataTable
        :columns="columns"
        :rows="projects"
        :loading="loading"
        @sort="handleSort"
      >
        <template #name="{ row }">
          <span class="font-semibold text-white block">{{ row.name }}</span>
          <span class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ row.description || 'Tanpa deskripsi.' }}</span>
        </template>
        <template #status="{ row }">
          <span
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider',
              row.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-white/5 text-gray-400 border border-white/10'
            ]"
          >
            {{ row.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
          </span>
        </template>
        <template #applications_count="{ row }">
          <span class="font-mono text-sm">{{ row.applications_count }}</span>
        </template>
        <template #created_at="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
        <template #action="{ row }">
          <div class="flex items-center gap-2">
            <router-link
              :to="`/projects/${row.id}/edit`"
              class="text-xs bg-white/5 border border-white/10 hover:bg-white/10 text-white font-medium px-3 py-1.5 rounded-lg transition-all"
            >
              Edit
            </router-link>
            <BaseButton
              v-if="row.applications_count === 0"
              variant="danger"
              size="sm"
              customClass="px-3 py-1.5"
              @click="confirmDelete(row)"
            >
              Hapus
            </BaseButton>
          </div>
        </template>
      </DataTable>

      <!-- Pagination -->
      <Pagination
        v-if="pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :per-page="pagination.per_page"
        @change="handlePageChange"
      />
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Hapus Proyek"
      :message="`Apakah Anda yakin ingin menghapus proyek '${projectToDelete?.name}'? Tindakan ini tidak dapat dibatalkan.`"
      confirm-label="Hapus Proyek"
      variant="danger"
      @confirm="deleteProject"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import DataTable from '../../components/DataTable.vue'
import Pagination from '../../components/Pagination.vue'
import BaseButton from '../../components/BaseButton.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'

const { success, error: toastError } = useToast()

const loading = ref(true)
const projects = ref([])
const filters = ref({
  search: '',
  status: '',
  page: 1,
  sort_by: 'created_at',
  order: 'desc'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const columns = [
  { key: 'name', label: 'Nama Proyek', sortable: true },
  { key: 'status', label: 'Status' },
  { key: 'applications_count', label: 'Jumlah Permohonan' },
  { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
  { key: 'action', label: 'Aksi' }
]

const statusOptions = [
  { value: 'active', label: 'Aktif' },
  { value: 'inactive', label: 'Non-Aktif' }
]

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const fetchProjects = async () => {
  loading.value = true
  try {
    const response = await api.get('/api/projects', { params: filters.value })
    projects.value = response.data.data || []
    if (response.data.meta) {
      pagination.value = response.data.meta
    }
  } catch (err) {
    toastError('Gagal mengambil daftar proyek.')
  } finally {
    loading.value = false
  }
}

const handleFilterChange = () => {
  filters.value.page = 1
  fetchProjects()
}

const handlePageChange = (page) => {
  filters.value.page = page
  fetchProjects()
}

const handleSort = ({ key, direction }) => {
  filters.value.sort_by = key
  filters.value.order = direction
  fetchProjects()
}

// Delete Logic
const showDeleteModal = ref(false)
const projectToDelete = ref(null)

const confirmDelete = (project) => {
  projectToDelete.value = project
  showDeleteModal.value = true
}

const deleteProject = async () => {
  if (!projectToDelete.value) return
  try {
    await api.delete(`/api/projects/${projectToDelete.value.id}`)
    success('Proyek berhasil dihapus.')
    showDeleteModal.value = false
    fetchProjects()
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal menghapus proyek.')
  }
}

onMounted(() => {
  fetchProjects()
})
</script>
