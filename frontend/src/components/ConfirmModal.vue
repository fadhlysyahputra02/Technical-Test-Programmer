<template>
  <BaseModal
    :show="show"
    :title="title"
    size="sm"
    @close="$emit('cancel')"
  >
    <div class="flex flex-col items-center text-center py-2">
      <!-- Warning or Danger Icon based on variant -->
      <div
        :class="[
          'p-3.5 rounded-full mb-4 bg-white/5 border',
          variant === 'danger' ? 'text-red-400 border-red-500/20' : 'text-purple-400 border-purple-500/20'
        ]"
      >
        <svg v-if="variant === 'danger'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <svg v-else class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <p class="text-sm text-gray-300 leading-relaxed">{{ message }}</p>
    </div>

    <template #footer>
      <BaseButton variant="ghost" @click="$emit('cancel')">Batal</BaseButton>
      <BaseButton :variant="variant" @click="$emit('confirm')">{{ confirmLabel }}</BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import BaseModal from './BaseModal.vue'
import BaseButton from './BaseButton.vue'

defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  confirmLabel: {
    type: String,
    default: 'Konfirmasi'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'danger'].includes(value)
  }
})

defineEmits(['confirm', 'cancel'])
</script>
