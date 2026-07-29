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
      <input
        :type="type"
        :value="modelValue"
        :required="required"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'w-full bg-[#161622] border rounded-lg px-4 py-3 text-sm focus:outline-none transition-colors disabled:opacity-50 disabled:pointer-events-none',
          error
            ? 'border-red-500 focus:border-red-500'
            : 'border-white/[0.08] focus:border-purple-500'
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
      />
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
  error: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
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
