<template>
  <div class="space-y-8">
    <!-- Header banner -->
    <div class="bg-white/[0.02] border border-white/[0.08] rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden shadow-2xl">
      <!-- Glow effect -->
      <div class="absolute -top-12 -left-12 w-32 h-32 rounded-full bg-indigo-600/10 blur-2xl pointer-events-none"></div>
      
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
        <div class="flex flex-col sm:flex-row items-center gap-5">
          <div class="h-16 w-16 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-2xl font-bold text-white shadow-xl shadow-purple-600/10">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="text-center sm:text-left">
            <h2 class="text-2xl font-bold text-white tracking-tight">Selamat Datang, {{ user?.name }}</h2>
            <p class="text-xs text-gray-400 mt-1">Reviewer Panel &bull; {{ user?.email }}</p>
            <div class="mt-3">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/20 uppercase tracking-wider">
                Penilai (Reviewer)
              </span>
            </div>
          </div>
        </div>

        <div>
          <router-link
            to="/reviewer/applications"
            class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg text-sm font-semibold shadow-lg shadow-purple-600/10 transition-all flex items-center gap-2"
          >
            Lihat Semua Antrean
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
      <DashboardStatCard
        label="Total Masuk"
        :value="summary.total_incoming || 0"
        color="purple"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
      />
      <DashboardStatCard
        label="Menunggu Review"
        :value="summary.pending_review || 0"
        color="blue"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
      />
      <DashboardStatCard
        label="Disetujui"
        :value="summary.approved || 0"
        color="emerald"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
      />
      <DashboardStatCard
        label="Butuh Revisi"
        :value="summary.revision_requested || 0"
        color="amber"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/></svg>'
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
      <h3 class="text-lg font-bold text-white mb-6">Keputusan Evaluasi Bulanan (12 Bulan Terakhir)</h3>
      <div class="h-[300px] w-full relative">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>

    <!-- Incoming / Unreviewed Applications Queue Table -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-white">Antrean Permohonan Perlu Ditinjau</h3>
        <router-link to="/reviewer/applications" class="text-xs text-purple-400 hover:underline">Lihat Antrean</router-link>
      </div>
      <DataTable :columns="tableColumns" :rows="incomingApplications" :loading="loading">
        <template #application_number="{ row }">
          <router-link :to="`/reviewer/applications/${row.id}`" class="text-purple-400 hover:underline font-semibold font-mono">
            {{ row.application_number }}
          </router-link>
        </template>
        <template #project="{ row }">
          {{ row.project?.name || '–' }}
        </template>
        <template #applicant="{ row }">
          {{ row.applicant?.name || '–' }}
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
            class="text-xs bg-purple-500/10 border border-purple-500/20 hover:bg-purple-500/20 text-purple-300 font-medium px-3.5 py-2 rounded-lg transition-all"
          >
            Tinjau
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
const incomingApplications = ref([])
const chartCanvas = ref(null)
let chartInstance = null

const tableColumns = [
  { key: 'application_number', label: 'Nomor Permohonan' },
  { key: 'project', label: 'Proyek' },
  { key: 'applicant', label: 'Pemohon' },
  { key: 'status', label: 'Status' },
  { key: 'submitted_at', label: 'Tanggal Submit' },
  { key: 'action', label: 'Aksi' }
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

const fetchDashboardData = async () => {
  try {
    const response = await api.get('/api/dashboard')
    const { summary: sum, chart_monthly: chartData, recent_applications: recent } = response.data
    summary.value = sum || {}
    incomingApplications.value = recent || []
    
    renderChart(chartData || [])
  } catch (err) {
    console.error('Gagal memuat data dashboard reviewer:', err)
  } finally {
    loading.value = false
  }
}

const renderChart = (chartData) => {
  if (!chartCanvas.value) return

  if (chartInstance) {
    chartInstance.destroy()
  }

  const labels = chartData.map((d) => d.month)
  const approved = chartData.map((d) => d.approved)
  const rejected = chartData.map((d) => d.rejected)
  const revision = chartData.map((d) => d.revision)

  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Disetujui',
          data: approved,
          backgroundColor: 'rgba(16, 185, 129, 0.4)',
          borderColor: 'rgb(16, 185, 129)',
          borderWidth: 1.5,
          borderRadius: 4
        },
        {
          label: 'Butuh Revisi',
          data: revision,
          backgroundColor: 'rgba(245, 158, 11, 0.4)',
          borderColor: 'rgb(245, 158, 11)',
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
