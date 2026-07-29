<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-4xl mx-auto space-y-8">
      <!-- Back Link -->
      <router-link to="/applications" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Daftar Permohonan
      </router-link>

      <!-- Loading Details -->
      <div v-if="fetching" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
              Edit Permohonan
            </h1>
            <p class="text-sm text-gray-400 mt-1 font-mono">Nomor: {{ application.application_number }} (Versi {{ application.version }})</p>
          </div>
          <StatusBadge :status="application.status" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Form & File Uploader -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Details Form Card -->
            <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl">
              <h3 class="text-lg font-bold text-white mb-4">Detail Pengajuan</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Proyek Terkait</label>
                  <p class="text-sm text-white font-medium bg-white/5 border border-white/10 rounded-lg px-4 py-3">
                    {{ application.project?.name || '–' }}
                  </p>
                </div>

                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Catatan Permohonan</label>
                  <textarea
                    v-model="notes"
                    placeholder="Masukkan penjelasan tambahan atau catatan untuk penilai..."
                    rows="4"
                    class="w-full bg-[#161622] border border-white/[0.08] rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-purple-500 transition-colors"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- File Upload Card -->
            <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
              <h3 class="text-lg font-bold text-white">Unggah Dokumen Pendukung</h3>
              <p class="text-xs text-gray-400">Harap unggah dokumen yang diperlukan. Setidaknya dibutuhkan 1 dokumen sebelum mengajukan permohonan.</p>
              
              <FileUploader
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                :maxSize="10"
                :multiple="true"
                @upload="handleFileUpload"
              />
            </div>
          </div>

          <!-- Document List & Actions -->
          <div class="space-y-6">
            <!-- Uploaded Documents Card -->
            <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl flex flex-col max-h-[450px]">
              <h3 class="text-lg font-bold text-white mb-4">Dokumen Terlampir</h3>
              
              <div class="flex-grow overflow-y-auto space-y-3 pr-1">
                <div
                  v-for="doc in application.documents"
                  :key="doc.id"
                  class="border border-white/[0.08] rounded-lg p-3 bg-white/[0.01] flex items-center justify-between gap-3"
                >
                  <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate">{{ doc.file_name }}</p>
                    <p class="text-[10px] text-gray-500">{{ formatSize(doc.file_size) }}</p>
                  </div>
                  <button
                    class="text-red-400 hover:text-red-300 p-1 shrink-0 transition-colors"
                    @click="confirmDeleteDoc(doc)"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <div v-if="!application.documents || application.documents.length === 0" class="text-center py-8 text-xs text-gray-500 italic">
                  Belum ada dokumen diunggah.
                </div>
              </div>
            </div>

            <!-- Form Submissions Actions -->
            <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 shadow-xl space-y-4">
              <BaseButton
                variant="secondary"
                customClass="w-full justify-center"
                :loading="saving"
                @click="saveDraft"
              >
                Simpan Draft
              </BaseButton>

              <BaseButton
                variant="primary"
                customClass="w-full justify-center"
                :disabled="!application.documents || application.documents.length === 0"
                @click="showSubmitModal = true"
              >
                Submit Permohonan
              </BaseButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Submit Modal -->
    <ConfirmModal
      :show="showSubmitModal"
      title="Submit Permohonan"
      message="Apakah Anda yakin ingin mengajukan permohonan ini? Setelah diajukan, permohonan tidak dapat diedit sampai mendapatkan penilaian."
      confirm-label="Ajukan Sekarang"
      variant="primary"
      @confirm="submitApplication"
      @cancel="showSubmitModal = false"
    />

    <!-- Confirm Delete Document Modal -->
    <ConfirmModal
      :show="showDeleteDocModal"
      title="Hapus Dokumen"
      :message="`Hapus dokumen '${docToDelete?.file_name}'?`"
      confirm-label="Hapus"
      variant="danger"
      @confirm="deleteDocument"
      @cancel="showDeleteDocModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import StatusBadge from '../../components/StatusBadge.vue'
import BaseButton from '../../components/BaseButton.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import FileUploader from '../../components/FileUploader.vue'

const router = useRouter()
const route = useRoute()
const { success, error: toastError } = useToast()

const fetching = ref(true)
const saving = ref(false)
const application = ref({})
const notes = ref('')

const applicationId = route.params.id

const showSubmitModal = ref(false)
const showDeleteDocModal = ref(false)
const docToDelete = ref(null)

const formatSize = (bytes) => {
  if (!bytes) return '–'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(2) + ' MB'
}

const fetchApplicationDetails = async () => {
  try {
    const response = await api.get(`/api/applications/${applicationId}`)
    application.value = response.data.data
    notes.value = response.data.data.notes || ''
  } catch (err) {
    toastError('Gagal memuat detail permohonan.')
    router.push('/applications')
  } finally {
    fetching.value = false
  }
}

const saveDraft = async () => {
  saving.value = true
  try {
    await api.put(`/api/applications/${applicationId}`, { notes: notes.value })
    success('Draft permohonan berhasil disimpan.')
  } catch (err) {
    toastError('Gagal menyimpan draft permohonan.')
  } finally {
    saving.value = false
  }
}

const handleFileUpload = async (files) => {
  for (let i = 0; i < files.length; i++) {
    const formData = new FormData()
    formData.append('file', files[i])
    
    try {
      await api.post(`/api/applications/${applicationId}/documents`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } catch (err) {
      toastError(`Gagal mengunggah file ${files[i].name}.`)
    }
  }
  
  success('Dokumen berhasil diunggah.')
  fetchApplicationDetails() // Refresh doc list
}

const confirmDeleteDoc = (doc) => {
  docToDelete.value = doc
  showDeleteDocModal.value = true
}

const deleteDocument = async () => {
  if (!docToDelete.value) return
  try {
    await api.delete(`/api/applications/${applicationId}/documents/${docToDelete.value.id}`)
    success('Dokumen berhasil dihapus.')
    showDeleteDocModal.value = false
    fetchApplicationDetails()
  } catch (err) {
    toastError('Gagal menghapus dokumen.')
  }
}

const submitApplication = async () => {
  showSubmitModal.value = false
  try {
    // Save draft notes first
    await api.put(`/api/applications/${applicationId}`, { notes: notes.value })
    
    // Submit application
    await api.post(`/api/applications/${applicationId}/submit`)
    success('Permohonan berhasil diajukan!')
    router.push('/applications')
  } catch (err) {
    toastError(err.response?.data?.message || 'Gagal mengajukan permohonan.')
  }
}

onMounted(() => {
  fetchApplicationDetails()
})
</script>
