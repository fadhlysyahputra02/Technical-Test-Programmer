<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] flex flex-col justify-center items-center px-6 py-12 relative overflow-hidden">
    <!-- Background Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="text-center max-w-md relative z-10">
      <h1 class="text-8xl font-black text-red-500/20 mb-4 select-none">403</h1>
      <h2 class="text-2xl font-bold mb-2">Akses Ditolak</h2>
      <p class="text-gray-400 text-sm mb-8">Anda tidak memiliki izin (role/permission) untuk mengakses halaman ini.</p>
      
      <router-link
        to="/dashboard"
        class="inline-block bg-white/[0.05] border border-white/[0.08] hover:bg-white/[0.08] text-white text-sm font-semibold px-6 py-3 rounded-lg transition-all"
      >
        Kembali ke Dashboard
      </router-link>
    </div>
  </div>
</template>

<script setup>
// Static 403 Page
</script>
