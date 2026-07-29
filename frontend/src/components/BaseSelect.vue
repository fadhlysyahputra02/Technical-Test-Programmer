<template>
  <div class="flex flex-col w-full">
    <label
      v-if="label"
      class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-red-400">*</span>
    </label>
    <div class="relative">
      <select
        :value="modelValue"
        :disabled="disabled"
        :class="[
          'w-full bg-[#161622] border rounded-lg px-4 py-3 text-sm focus:outline-none transition-colors appearance-none cursor-pointer disabled:opacity-50 disabled:pointer-events-none',
          error
            ? 'border-red-500 focus:border-red-500'
            : 'border-white/[0.08] focus:border-purple-500'
        ]"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      <!-- Custom Chevron icon -->
      <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
          <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
        </svg>
      </div>
    </div>
    <span v-if="error" class="text-xs text-red-400 mt-1.5 ml-1">
      {{ error }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    required: true,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Pilih salah satu...'
  },
  error: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])
</script>
