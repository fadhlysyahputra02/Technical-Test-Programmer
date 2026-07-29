<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] p-8">
    <div class="max-w-xl mx-auto space-y-8">
      <!-- Back Link -->
      <router-link to="/projects" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Daftar Proyek
      </router-link>

      <div>
        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">Buat Proyek Baru</h1>
        <p class="text-sm text-gray-400 mt-1">Tambahkan detail proyek baru untuk pengajuan permohonan Anda</p>
      </div>

      <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-8 shadow-2xl relative">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <BaseInput
            label="Nama Proyek"
            v-model="form.name"
            required
            placeholder="Masukkan nama proyek Anda"
            :error="errors.name?.[0] || clientErrors.name"
          />

          <div class="flex flex-col">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Deskripsi Proyek</label>
            <textarea
              v-model="form.description"
              placeholder="Jelaskan deskripsi proyek Anda..."
              rows="5"
              class="w-full bg-[#161622] border border-white/[0.08] rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-purple-500 transition-colors"
            ></textarea>
          </div>

          <BaseSelect
            label="Status Awal"
            v-model="form.status"
            :options="statusOptions"
            :placeholder="null"
          />

          <div v-if="generalError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
            {{ generalError }}
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <router-link to="/projects">
              <BaseButton variant="ghost">Batal</BaseButton>
            </router-link>
            <BaseButton
              type="submit"
              :loading="loading"
            >
              Simpan Proyek
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'
import { useToast } from '../../composables/useToast'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import BaseButton from '../../components/BaseButton.vue'

const router = useRouter()
const { success, error: toastError } = useToast()

const loading = ref(false)
const form = ref({
  name: '',
  description: '',
  status: 'active'
})

const errors = ref({})
const clientErrors = ref({})
const generalError = ref(null)

const statusOptions = [
  { value: 'active', label: 'Aktif' },
  { value: 'inactive', label: 'Non-Aktif' }
]

const validate = () => {
  clientErrors.value = {}
  let isValid = true

  if (!form.value.name.trim()) {
    clientErrors.value.name = 'Nama proyek wajib diisi.'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validate()) return

  loading.value = true
  errors.value = {}
  generalError.value = null

  try {
    await api.post('/api/projects', form.value)
    success('Proyek baru berhasil dibuat.')
    router.push('/projects')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'Gagal menyimpan proyek.'
    }
  } finally {
    loading.value = false
  }
}
</script>
