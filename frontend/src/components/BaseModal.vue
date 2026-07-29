<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="$emit('close')"
      >
        <Transition
          enter-active-class="transition duration-300 ease-out transform"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in transform"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            :class="[
              'w-full bg-[#12121A] border border-white/[0.08] rounded-xl flex flex-col shadow-2xl relative max-h-[90vh]',
              sizeClasses[size]
            ]"
          >
            <!-- Header -->
            <div class="px-6 py-5 border-b border-white/[0.08] flex items-center justify-between">
              <h3 class="text-lg font-bold text-white">{{ title }}</h3>
              <button
                class="text-gray-400 hover:text-white transition-colors"
                @click="$emit('close')"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 overflow-y-auto flex-1 text-sm text-gray-300">
              <slot></slot>
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="px-6 py-4 border-t border-white/[0.08] bg-white/[0.01] rounded-b-xl flex justify-end gap-3"
            >
              <slot name="footer"></slot>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  }
})

defineEmits(['close'])

const sizeClasses = {
  sm: 'max-w-md',
  md: 'max-w-xl',
  lg: 'max-w-3xl',
  xl: 'max-w-5xl'
}
</script>
