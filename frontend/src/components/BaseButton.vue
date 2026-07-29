<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-semibold rounded-lg transition-all active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-purple-500/20 disabled:opacity-50 disabled:pointer-events-none',
      sizeClasses[size],
      variantClasses[variant],
      customClass
    ]"
    @click="$emit('click', $event)"
  >
    <!-- Spinner for loading state -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <slot v-else></slot>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  },
  customClass: {
    type: String,
    default: ''
  }
})

defineEmits(['click'])

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2.5 text-sm',
  lg: 'px-6 py-3.5 text-base'
}

const variantClasses = {
  primary: 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg shadow-purple-600/10 hover:shadow-purple-600/25 border border-transparent',
  secondary: 'bg-white/[0.04] border border-white/[0.08] hover:bg-white/[0.08] text-white',
  danger: 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white shadow-lg shadow-red-600/10 hover:shadow-red-600/25 border border-transparent',
  ghost: 'bg-transparent hover:bg-white/[0.04] border border-transparent text-gray-300 hover:text-white'
}
</script>
