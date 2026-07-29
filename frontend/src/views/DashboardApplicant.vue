<template>
  <div class="space-y-8">
    <!-- Profile Card (Premium Layout) -->
    <div class="bg-white/[0.02] border border-white/[0.08] rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden shadow-2xl">
      <!-- Glow effect -->
      <div class="absolute -top-12 -left-12 w-32 h-32 rounded-full bg-purple-600/10 blur-2xl pointer-events-none"></div>
      
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
        <div class="flex flex-col sm:flex-row items-center gap-5">
          <div class="h-16 w-16 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-2xl font-bold text-white shadow-xl shadow-purple-600/10">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="text-center sm:text-left">
            <h2 class="text-2xl font-bold text-white tracking-tight">{{ user?.name }}</h2>
            <p class="text-xs text-gray-400 mt-1">{{ user?.email }}</p>
            <div class="mt-3">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/20 uppercase tracking-wider">
                Pemohon (Applicant)
              </span>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <router-link
            to="/projects"
            class="px-4 py-2 border border-white/[0.08] hover:bg-white/[0.04] text-white rounded-lg text-sm font-semibold transition-all"
          >
            Lihat Proyek
          </router-link>
          <router-link
            to="/applications/create"
            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg text-sm font-semibold shadow-lg shadow-purple-600/10 transition-all"
          >
            Buat Permohonan
          </router-link>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <DashboardStatCard
        label="Total Pengajuan"
        :value="summary.total || 0"
        color="purple"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
      />
      <DashboardStatCard
        label="Draft"
        :value="summary.draft || 0"
        color="gray"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
      />
      <DashboardStatCard
        label="Diajukan"
        :value="summary.submitted || 0"
        color="blue"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
      />
      <DashboardStatCard
        label="Sedang Ditinjau"
        :value="summary.under_review || 0"
        color="amber"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
      />
      <DashboardStatCard
        label="Butuh Revisi"
        :value="summary.revision_requested || 0"
        color="rose"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/></svg>'
      />
      <DashboardStatCard
        label="Disetujui"
        :value="summary.approved || 0"
        color="emerald"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
      />
      <DashboardStatCard
        label="Ditolak"
        :value="summary.rejected || 0"
        color="rose"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
      />
    </div>

    <!-- Analytics & Chart Section -->
    <div class="bg-white/[0.01] border border-white/[0.08] rounded-2xl p-6 backdrop-blur-md">
      <h3 class="text-lg font-bold text-white mb-6">Tren Pengajuan Bulanan (12 Bulan Terakhir)</h3>
      <div class="h-[300px] w-full relative">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>

    <!-- Recent Applications Table -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-white">5 Permohonan Terbaru</h3>
        <router-link to="/applications" class="text-xs text-purple-400 hover:underline">Lihat Semua</router-link>
      </div>
      <DataTable :columns="tableColumns" :rows="recentApplications" :loading="loading">
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
        <template #created_at="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
        <template #action="{ row }">
          <router-link
            :to="`/applications/${row.id}`"
            class="text-xs bg-white/5 border border-white/10 hover:bg-white/10 text-white font-medium px-3 py-1.5 rounded-lg transition-all"
          >
            Detail
          </router-link>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Chart from 'chart.js/auto'
import api from '../utils/api'
import DashboardStatCard from '../components/DashboardStatCard.vue'
import DataTable from '../components/DataTable.vue'
import StatusBadge from '../components/StatusBadge.vue'

defineProps({
  user: {
    type: Object,
    required: true
  }
})

const loading = ref(true)
const summary = ref({})
const recentApplications = ref([])
const chartCanvas = ref(null)
let chartInstance = null

const tableColumns = [
  { key: 'application_number', label: 'Nomor Permohonan' },
  { key: 'project', label: 'Proyek' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Tanggal Dibuat' },
  { key: 'action', label: 'Aksi' }
]

const formatDate = (dateStr) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const fetchDashboardData = async () => {
  try {
    const response = await api.get('/api/dashboard')
    const { summary: sum, chart_monthly: chartData, recent_applications: recent } = response.data
    summary.value = sum || {}
    recentApplications.value = recent || []
    
    renderChart(chartData || [])
  } catch (err) {
    console.error('Gagal mengambil data dashboard applicant:', err)
  } finally {
    loading.value = false
  }
}

const renderChart = (chartData) => {
  if (!chartCanvas.value) return

  // Destroy previous instance if any
  if (chartInstance) {
    chartInstance.destroy()
  }

  const labels = chartData.map((d) => d.month)
  const totals = chartData.map((d) => d.total)
  const approved = chartData.map((d) => d.approved)
  const rejected = chartData.map((d) => d.rejected)

  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Total Pengajuan',
          data: totals,
          backgroundColor: 'rgba(147, 51, 234, 0.4)',
          borderColor: 'rgb(147, 51, 234)',
          borderWidth: 1.5,
          borderRadius: 4
        },
        {
          label: 'Disetujui',
          data: approved,
          backgroundColor: 'rgba(16, 185, 129, 0.4)',
          borderColor: 'rgb(16, 185, 129)',
          borderWidth: 1.5,
          borderRadius: 4
        },
        {
          label: 'Ditolak',
          data: rejected,
          backgroundColor: 'rgba(244, 63, 94, 0.4)',
          borderColor: 'rgb(244, 63, 94)',
          borderWidth: 1.5,
          borderRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: '#9CA3AF',
            font: { family: 'Inter, system-ui' }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255, 255, 255, 0.05)' },
          ticks: { color: '#9CA3AF' }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.05)' },
          ticks: { color: '#9CA3AF', stepSize: 1 }
        }
      }
    }
  })
}

onMounted(() => {
  fetchDashboardData()
})

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>
