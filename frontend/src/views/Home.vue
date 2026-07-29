<template>
  <div class="min-h-screen bg-[#0F0F16] text-[#E4E4ED] flex flex-col justify-between selection:bg-purple-600 selection:text-white">
    <!-- Header -->
    <header class="border-b border-white/[0.08] backdrop-blur-md sticky top-0 z-50 bg-[#0F0F16]/80 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
          <span class="font-bold text-white text-lg">LS</span>
        </div>
        <span class="font-semibold text-lg tracking-wide bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">LionStyle Stack</span>
      </div>
      <nav class="flex items-center gap-4">
        <router-link to="/login" class="px-5 py-2 rounded-xl text-sm font-medium border border-white/[0.08] bg-white/[0.02] hover:bg-white/[0.06] transition-all duration-300">
          Sign In
        </router-link>
        <router-link to="/dashboard" class="px-5 py-2 rounded-xl text-sm font-medium bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg shadow-purple-600/20 hover:shadow-purple-600/30 transition-all duration-300">
          Dashboard
        </router-link>
      </nav>
    </header>

    <!-- Main Hero -->
    <main class="flex-grow flex items-center justify-center px-6 py-12 relative overflow-hidden">
      <!-- Background Glows -->
      <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-700/20 rounded-full blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 bg-indigo-700/20 rounded-full blur-[120px] pointer-events-none"></div>

      <div class="max-w-4xl w-full text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-purple-500/20 bg-purple-500/10 text-purple-300 text-xs font-semibold mb-6 animate-pulse">
          <span>✨ Setup Completed & Configured</span>
        </div>
        
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-6 bg-gradient-to-b from-white via-gray-100 to-gray-500 bg-clip-text text-transparent">
          Laravel 12 + Vue 3 <br class="hidden sm:inline" />
          <span class="bg-gradient-to-r from-purple-400 to-indigo-400 bg-clip-text text-transparent">Docker Stack</span>
        </h1>
        
        <p class="text-gray-400 text-lg max-w-xl mx-auto mb-10 leading-relaxed">
          A high-performance modern web boilerplate pre-configured with PostgreSQL, Sanctum Auth, Pinia, Tailwind CSS v4, and Spatie Permissions.
        </p>

        <!-- Tech Stack Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto text-left">
          <!-- Backend Card -->
          <div class="p-5 rounded-2xl border border-white/[0.06] bg-white/[0.02] hover:bg-white/[0.04] transition-all duration-300">
            <div class="text-red-400 font-bold mb-2">Backend</div>
            <div class="font-semibold text-white">Laravel 13</div>
            <div class="text-xs text-gray-500 mt-1">PHP 8.3 & Sanctum</div>
          </div>
          <!-- Frontend Card -->
          <div class="p-5 rounded-2xl border border-white/[0.06] bg-white/[0.02] hover:bg-white/[0.04] transition-all duration-300">
            <div class="text-emerald-400 font-bold mb-2">Frontend</div>
            <div class="font-semibold text-white">Vue 3</div>
            <div class="text-xs text-gray-500 mt-1">Vite, Router & Pinia</div>
          </div>
          <!-- Database Card -->
          <div class="p-5 rounded-2xl border border-white/[0.06] bg-white/[0.02] hover:bg-white/[0.04] transition-all duration-300">
            <div class="text-blue-400 font-bold mb-2">Database</div>
            <div class="font-semibold text-white">PostgreSQL</div>
            <div class="text-xs text-gray-500 mt-1">Persistent volume</div>
          </div>
          <!-- Styling Card -->
          <div class="p-5 rounded-2xl border border-white/[0.06] bg-white/[0.02] hover:bg-white/[0.04] transition-all duration-300">
            <div class="text-cyan-400 font-bold mb-2">Styling</div>
            <div class="font-semibold text-white">Tailwind CSS</div>
            <div class="text-xs text-gray-500 mt-1">Version 4 (Vite)</div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/[0.08] py-6 px-6 text-center text-xs text-gray-500">
      <p>&copy; 2026 LionStyle Technical Test. Built with Antigravity.</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'Home'
}
</script>
