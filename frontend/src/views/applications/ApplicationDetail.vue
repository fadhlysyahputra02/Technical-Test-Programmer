<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header / Nav -->
      <div class="flex items-center justify-between">
        <router-link to="/applications" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors duration-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali ke Daftar Permohonan
        </router-link>

        <router-link
          v-if="isEditable"
          :to="`/applications/${application.id}/edit`"
          class="bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all"
        >
          Edit Permohonan
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <!-- Main Content Grid -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details & Documents (Left Columns) -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Information Card -->
          <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-6">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h2 class="text-2xl font-bold text-white">Detail Permohonan</h2>
                <p class="text-xs text-gray-400 mt-1 font-mono">{{ application.application_number }} (Versi {{ application.version }})</p>
              </div>
              <StatusBadge :status="application.status" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-white/[0.04]">
              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Nama Proyek</span>
                <span class="text-sm font-semibold text-white">{{ application.project?.name }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Pemohon</span>
                <span class="text-sm font-semibold text-white">{{ application.applicant?.name }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Tanggal Dibuat</span>
                <span class="text-sm text-gray-300">{{ formatDate(application.created_at) }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Tanggal Pengajuan</span>
                <span class="text-sm text-gray-300">{{ formatDate(application.submitted_at) }}</span>
              </div>
            </div>

            <!-- Notes Section -->
            <div v-if="application.notes" class="pt-4 border-t border-white/[0.04]">
              <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2">Catatan Pemohon</span>
              <p class="text-sm text-gray-300 leading-relaxed bg-white/[0.01] border border-white/[0.06] rounded-lg p-4 italic">
                "{{ application.notes }}"
              </p>
            </div>
          </div>

          <!-- Documents Card -->
          <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
            <h3 class="text-lg font-bold text-white">Dokumen Pendukung</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div
                v-for="doc in application.documents"
                :key="doc.id"
                class="border border-white/[0.08] rounded-lg p-4 bg-white/[0.01] flex items-center justify-between gap-4"
              >
                <div class="overflow-hidden">
                  <p class="text-xs font-semibold text-white truncate">{{ doc.file_name }}</p>
                  <p class="text-[10px] text-gray-500">{{ formatSize(doc.file_size) }}</p>
                </div>
                
                <BaseButton
                  variant="secondary"
                  size="sm"
                  customClass="px-3 py-1.5 shrink-0"
                  @click="downloadFile(doc)"
                >
                  Unduh
                </BaseButton>
              </div>

              <div v-if="!application.documents || application.documents.length === 0" class="sm:col-span-2 text-center py-10 text-sm text-gray-500 italic">
                Tidak ada dokumen pendukung terlampir.
              </div>
            </div>
          </div>

          <!-- Reviewer Decisions Outcomes -->
          <div v-if="application.reviews && application.reviews.length > 0" class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
            <h3 class="text-lg font-bold text-white">Keputusan Penilaian</h3>
            
            <div class="space-y-4">
              <div
                v-for="review in application.reviews"
                :key="review.id"
                class="border border-white/[0.08] bg-white/[0.01] rounded-lg p-5"
              >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                  <div class="flex items-center gap-2">
                    <span :class="['text-xs font-semibold px-2 py-0.5 rounded border', getDecisionClasses(review.decision)]">
                      {{ formatDecision(review.decision) }}
                    </span>
                  </div>
                  <span class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-300 leading-relaxed italic mb-4">"{{ review.notes }}"</p>
                <div class="flex items-center gap-1 text-xs text-gray-400">
                  <span>Penilai:</span>
                  <span class="font-semibold text-white">{{ review.reviewer?.name || 'Sistem' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- History Timeline (Right Column) -->
        <div class="space-y-6">
          <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-6">Linimasa Perubahan Status</h3>
            <HistoryTimeline :histories="application.status_histories || []" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import StatusBadge from '../../components/StatusBadge.vue'
import BaseButton from '../../components/BaseButton.vue'
import HistoryTimeline from '../../components/HistoryTimeline.vue'

const route = useRoute()
const router = useRouter()
const { error: toastError } = useToast()

const loading = ref(true)
const application = ref({})
const applicationId = route.params.id

const isEditable = computed(() => {
  const status = application.value.status
  return status === 'draft' || status === 'revision_requested'
})

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

const formatSize = (bytes) => {
  if (!bytes) return '–'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(2) + ' MB'
}

const formatDecision = (decision) => {
  const map = {
    approved: 'Disetujui',
    rejected: 'Ditolak',
    revision_requested: 'Butuh Revisi'
  }
  return map[decision] || decision
}

const getDecisionClasses = (decision) => {
  const map = {
    approved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    rejected: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    revision_requested: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'
  }
  return map[decision] || 'bg-gray-500/10 text-gray-400 border-gray-500/20'
}

const fetchApplicationDetails = async () => {
  try {
    const response = await api.get(`/api/applications/${applicationId}`)
    application.value = response.data.data
  } catch (err) {
    toastError('Gagal memuat rincian permohonan.')
    router.push('/applications')
  } finally {
    loading.value = false
  }
}

const downloadFile = async (doc) => {
  try {
    const response = await api.get(`/api/documents/${doc.id}/download`, {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: doc.file_type })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = doc.file_name
    link.click()
    window.URL.revokeObjectURL(link.href)
  } catch (err) {
    toastError('Gagal mengunduh file.')
  }
}

onMounted(() => {
  fetchApplicationDetails()
})
</script>
