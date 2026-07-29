<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header / Back Navigation -->
      <div class="flex items-center justify-between">
        <router-link to="/reviewer/applications" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors duration-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali ke Antrean Persetujuan
        </router-link>
      </div>

      <!-- Loading Details -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <!-- Main Layout Grid -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details & Review form (Left columns) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Information Card -->
          <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-6">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h2 class="text-2xl font-bold text-white">Detail Permohonan</h2>
                <p class="text-xs text-gray-400 mt-1 font-mono">Nomor: {{ application.application_number }} (Versi {{ application.version }})</p>
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
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Tanggal Diajukan</span>
                <span class="text-sm text-gray-300">{{ formatDate(application.submitted_at) }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Penilai Terakhir</span>
                <span class="text-sm text-gray-300">{{ application.latest_reviewer?.name || 'Belum Dinilai' }}</span>
              </div>
            </div>

            <!-- Notes Section -->
            <div v-if="application.notes" class="pt-4 border-t border-white/[0.04]">
              <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 mb-2">Catatan Pemohon</span>
              <p class="text-sm text-gray-300 bg-white/[0.01] border border-white/[0.06] rounded-lg p-4 italic">
                "{{ application.notes }}"
              </p>
            </div>
          </div>

          <!-- Documents Card -->
          <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
            <h3 class="text-lg font-bold text-white">Dokumen Terlampir</h3>
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
                Tidak ada dokumen terlampir.
              </div>
            </div>
          </div>

          <!-- Evaluation Form (Only visible if submitted / under_review) -->
          <div
            v-if="canReview"
            class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-6"
          >
            <h3 class="text-lg font-bold text-white">Form Penilaian Permohonan</h3>
            
            <form @submit.prevent="handleReviewSubmit" class="space-y-5">
              <BaseSelect
                label="Keputusan Penilaian"
                v-model="reviewForm.decision"
                :options="decisionOptions"
                placeholder="Pilih keputusan review"
                required
                :error="errors.decision?.[0] || clientErrors.decision"
              />

              <div class="flex flex-col">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Catatan Evaluasi (Min 10 karakter)</label>
                <textarea
                  v-model="reviewForm.notes"
                  placeholder="Masukkan umpan balik penilaian rinci untuk pemohon..."
                  rows="4"
                  required
                  class="w-full bg-[#161622] border border-white/[0.08] rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-purple-500 transition-colors"
                ></textarea>
                <span v-if="clientErrors.notes" class="text-xs text-red-400 mt-1.5 ml-1">
                  {{ clientErrors.notes }}
                </span>
                <span v-if="errors.notes" class="text-xs text-red-400 mt-1.5 ml-1">
                  {{ errors.notes?.[0] }}
                </span>
              </div>

              <div v-if="generalError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
                {{ generalError }}
              </div>

              <div class="flex justify-end pt-2">
                <BaseButton
                  type="submit"
                  variant="primary"
                >
                  Kirim Keputusan Penilaian
                </BaseButton>
              </div>
            </form>
          </div>

          <!-- Previous Reviews Log -->
          <div v-if="application.reviews && application.reviews.length > 0" class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
            <h3 class="text-lg font-bold text-white">Riwayat Penilaian Sebelumnya</h3>
            
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
            <h3 class="text-lg font-bold text-white mb-6">Linimasa Status</h3>
            <HistoryTimeline :histories="application.status_histories || []" />
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Decision Modal -->
    <ConfirmModal
      :show="showConfirmModal"
      title="Kirim Keputusan Penilaian"
      message="Apakah Anda yakin ingin mengirim keputusan penilaian ini kepada pemohon?"
      confirm-label="Kirim Keputusan"
      variant="primary"
      @confirm="submitDecision"
      @cancel="showConfirmModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useReviewStore } from '../../stores/review'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import StatusBadge from '../../components/StatusBadge.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import HistoryTimeline from '../../components/HistoryTimeline.vue'

const route = useRoute()
const router = useRouter()
const reviewStore = useReviewStore()
const { success, error: toastError } = useToast()

const loading = ref(true)
const application = ref({})
const applicationId = route.params.id

const reviewForm = ref({
  decision: '',
  notes: ''
})

const errors = ref({})
const clientErrors = ref({})
const generalError = ref(null)
const showConfirmModal = ref(false)

const canReview = computed(() => {
  const status = application.value.status
  return status === 'submitted' || status === 'under_review'
})

const decisionOptions = [
  { value: 'approved', label: 'Setujui Permohonan' },
  { value: 'revision_requested', label: 'Minta Revisi' },
  { value: 'rejected', label: 'Tolak Permohonan' }
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
    toastError('Gagal memuat detail permohonan.')
    router.push('/reviewer/applications')
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

const handleReviewSubmit = () => {
  clientErrors.value = {}
  let isValid = true

  if (!reviewForm.value.decision) {
    clientErrors.value.decision = 'Keputusan review wajib dipilih.'
    isValid = false
  }

  if (reviewForm.value.notes.length < 10) {
    clientErrors.value.notes = 'Catatan evaluasi minimal 10 karakter.'
    isValid = false
  }

  if (isValid) {
    showConfirmModal.value = true
  }
}

const submitDecision = async () => {
  showConfirmModal.value = false
  errors.value = {}
  generalError.value = null

  try {
    await reviewStore.submitReview(applicationId, reviewForm.value)
    success('Penilaian permohonan berhasil dikirim!')
    
    // Reset review form fields
    reviewForm.value = {
      decision: '',
      notes: ''
    }
    
    // Refresh detail screen
    fetchApplicationDetails()
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'Gagal memproses penilaian.'
    }
  }
}

onMounted(() => {
  fetchApplicationDetails()
})
</script>
