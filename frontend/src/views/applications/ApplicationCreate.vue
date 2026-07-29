<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-xl mx-auto space-y-8">
      <!-- Back Link -->
      <router-link to="/applications" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Daftar Permohonan
      </router-link>

      <div>
        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">Buat Permohonan Baru</h1>
        <p class="text-sm text-gray-400 mt-1">Pilih salah satu proyek aktif untuk mulai membuat draft permohonan baru</p>
      </div>

      <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-8 shadow-2xl relative">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <BaseSelect
            label="Pilih Proyek"
            v-model="form.project_id"
            :options="projects"
            placeholder="Pilih proyek untuk permohonan ini"
            required
            :error="errors.project_id?.[0]"
          />

          <div v-if="generalError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
            {{ generalError }}
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <router-link to="/applications">
              <BaseButton variant="ghost">Batal</BaseButton>
            </router-link>
            <BaseButton
              type="submit"
              :loading="loading"
            >
              Buat Draft Permohonan
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import BaseSelect from '../../components/BaseSelect.vue'
import BaseButton from '../../components/BaseButton.vue'

const router = useRouter()
const { success, error: toastError } = useToast()

const loading = ref(false)
const projects = ref([])
const form = ref({
  project_id: ''
})

const errors = ref({})
const generalError = ref(null)

const fetchActiveProjects = async () => {
  try {
    const response = await api.get('/api/projects', { params: { status: 'active', limit: 100 } })
    projects.value = (response.data.data || []).map((p) => ({
      value: p.id,
      label: p.name
    }))
  } catch (err) {
    toastError('Gagal memuat daftar proyek aktif.')
  }
}

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  generalError.value = null

  try {
    const response = await api.post('/api/applications', form.value)
    success('Draft permohonan berhasil dibuat.')
    // Redirect to edit page of this newly created draft to manage files/details
    router.push(`/applications/${response.data.data.id}/edit`)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'Gagal membuat permohonan.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchActiveProjects()
})
</script>
