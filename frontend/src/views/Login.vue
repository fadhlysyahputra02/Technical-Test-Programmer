<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] flex flex-col justify-center items-center px-6 py-12 relative overflow-hidden">
    <!-- Background Glows -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-12 right-12 w-72 h-72 bg-blue-500/5 rounded-full blur-[80px] pointer-events-none"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md bg-white/[0.03] backdrop-blur-xl border border-white/[0.08] rounded-2xl p-8 relative z-10 shadow-2xl">
      <div class="text-center mb-8">
        <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20 mx-auto mb-4">
          <span class="font-bold text-white text-xl">LS</span>
        </div>
        <h2 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
          Selamat Datang
        </h2>
        <p class="text-sm text-gray-400 mt-2">Masuk ke Sistem Persetujuan Dokumen</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <BaseInput
          label="Alamat Email"
          v-model="form.email"
          type="email"
          required
          placeholder="nama@email.com"
          :error="errors.email?.[0]"
        />

        <BaseInput
          label="Password"
          v-model="form.password"
          type="password"
          required
          placeholder="Masukkan password Anda"
          :error="errors.password?.[0]"
        />

        <div v-if="generalError" class="text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
          {{ generalError }}
        </div>

        <BaseButton
          type="submit"
          :loading="loading"
          customClass="w-full py-3.5"
        >
          Masuk
        </BaseButton>
      </form>

      <div class="mt-6 text-center text-sm text-gray-400">
        Belum memiliki akun?
        <router-link to="/register" class="text-purple-400 hover:underline">Daftar Sekarang</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { useToast } from '../composables/useToast'
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue'

const router = useRouter()
const { login, loading } = useAuth()
const { success } = useToast()

const form = ref({
  email: '',
  password: ''
})

const errors = ref({})
const generalError = ref(null)

const handleSubmit = async () => {
  errors.value = {}
  generalError.value = null
  
  try {
    await login(form.value)
    success('Login berhasil! Selamat datang kembali.')
    router.push('/dashboard')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'Email atau password salah.'
    }
  }
}
</script>
