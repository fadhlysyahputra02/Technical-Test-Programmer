<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] flex flex-col justify-between">
    <!-- Header -->
    <header class="border-b border-white/[0.08] backdrop-blur-md sticky top-0 z-50 bg-[#0F0F16]/80 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
          <span class="font-bold text-white text-lg">LS</span>
        </div>
        <span class="font-semibold text-lg tracking-wide bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">Sistem Persetujuan Dokumen</span>
      </div>

      <!-- Navigation links for Applicant -->
      <nav v-if="authStore.role === 'applicant'" class="hidden md:flex items-center gap-6 text-sm text-gray-400 font-semibold">
        <router-link to="/dashboard" class="hover:text-white transition-colors" active-class="text-white">Dashboard</router-link>
        <router-link to="/projects" class="hover:text-white transition-colors" active-class="text-white">Proyek</router-link>
        <router-link to="/applications" class="hover:text-white transition-colors" active-class="text-white">Permohonan</router-link>
      </nav>

      <!-- Navigation links for Reviewer -->
      <nav v-if="authStore.role === 'reviewer'" class="hidden md:flex items-center gap-6 text-sm text-gray-400 font-semibold">
        <router-link to="/dashboard" class="hover:text-white transition-colors" active-class="text-white">Dashboard</router-link>
        <router-link to="/reviewer/applications" class="hover:text-white transition-colors" active-class="text-white">Persetujuan Masuk</router-link>
      </nav>

      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-400 hidden sm:inline">User: <strong class="text-white">{{ authStore.user?.email }}</strong></span>
        <BaseButton
          variant="secondary"
          size="sm"
          :loading="authStore.loading"
          @click="handleLogout"
        >
          Keluar
        </BaseButton>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-6xl w-full mx-auto px-6 py-8">
      <!-- Mobile navigation menu -->
      <div class="md:hidden flex justify-center gap-4 mb-6 py-2 border-b border-white/[0.04] text-xs font-semibold text-gray-400">
        <router-link to="/dashboard" class="hover:text-white" active-class="text-white">Dashboard</router-link>
        
        <template v-if="authStore.role === 'applicant'">
          <router-link to="/projects" class="hover:text-white" active-class="text-white">Proyek</router-link>
          <router-link to="/applications" class="hover:text-white" active-class="text-white">Permohonan</router-link>
        </template>
        
        <template v-if="authStore.role === 'reviewer'">
          <router-link to="/reviewer/applications" class="hover:text-white" active-class="text-white">Persetujuan</router-link>
        </template>
      </div>

      <!-- Render role specific dashboard wrapper -->
      <DashboardApplicant v-if="authStore.role === 'applicant'" :user="authStore.user" />
      <DashboardReviewer v-else-if="authStore.role === 'reviewer'" :user="authStore.user" />
      <div v-else class="text-center py-20 text-gray-500">
        Role Anda tidak terdefinisi. Silakan hubungi admin.
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/[0.08] py-6 px-6 text-center text-xs text-gray-500">
      <p>&copy; 2026 LionStyle Technical Test. Built with Vue 3 & Laravel 13.</p>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BaseButton from '../components/BaseButton.vue'
import DashboardApplicant from './DashboardApplicant.vue'
import DashboardReviewer from './DashboardReviewer.vue' // Placeholder created next

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (err) {
    console.error('Logout failed:', err)
  }
}
</script>
