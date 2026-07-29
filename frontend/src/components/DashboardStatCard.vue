<template>
  <div
    :class="[
      'relative border border-white/[0.08] rounded-xl p-6 bg-white/[0.01] backdrop-blur-md overflow-hidden transition-all hover:scale-[1.01]',
      colorStyles[color]?.cardGlow || 'hover:shadow-purple-500/5'
    ]"
  >
    <!-- Background subtle glow -->
    <div :class="['absolute -top-12 -right-12 w-28 h-28 rounded-full blur-[40px] opacity-10 pointer-events-none', colorStyles[color]?.glow || 'bg-purple-500']"></div>

    <div class="flex items-center justify-between mb-4">
      <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ label }}</span>
      <div :class="['p-2 rounded-lg bg-white/[0.03] border border-white/[0.08]', colorStyles[color]?.iconText || 'text-purple-400']">
        <component :is="icon" v-if="typeof icon === 'object'" class="w-5 h-5" />
        <span v-else v-html="icon" class="w-5 h-5 block"></span>
      </div>
    </div>

    <div class="flex items-end justify-between">
      <h3 class="text-3xl font-extrabold text-white tracking-tight">{{ value }}</h3>
      
      <!-- Optional Trend Badge -->
      <div
        v-if="trend"
        :class="[
          'flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded',
          trend.type === 'up' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'
        ]"
      >
        <svg v-if="trend.type === 'up'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
        </svg>
        {{ trend.value }}
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: [Object, String],
    required: true
  },
  color: {
    type: String,
    default: 'purple',
    validator: (value) => ['purple', 'blue', 'amber', 'emerald', 'rose', 'gray'].includes(value)
  },
  trend: {
    type: Object,
    default: null,
    validator: (value) => ['up', 'down'].includes(value?.type)
  }
})

const colorStyles = {
  purple: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(147,51,234,0.05)] hover:border-purple-500/30',
    glow: 'bg-purple-500',
    iconText: 'text-purple-400'
  },
  blue: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(59,130,246,0.05)] hover:border-blue-500/30',
    glow: 'bg-blue-500',
    iconText: 'text-blue-400'
  },
  amber: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(245,158,11,0.05)] hover:border-amber-500/30',
    glow: 'bg-amber-500',
    iconText: 'text-amber-400'
  },
  emerald: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(16,185,129,0.05)] hover:border-emerald-500/30',
    glow: 'bg-emerald-500',
    iconText: 'text-emerald-400'
  },
  rose: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(244,63,94,0.05)] hover:border-rose-500/30',
    glow: 'bg-rose-500',
    iconText: 'text-rose-400'
  },
  gray: {
    cardGlow: 'hover:shadow-[0_0_24px_rgba(255,255,255,0.02)] hover:border-white/20',
    glow: 'bg-gray-400',
    iconText: 'text-gray-400'
  }
}
</script>
