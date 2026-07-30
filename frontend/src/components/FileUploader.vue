<template>
  <div class="w-full">
    <!-- Drag & Drop Zone -->
    <div
      :class="[
        'border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all flex flex-col items-center justify-center',
        isDragging
          ? 'border-purple-500 bg-purple-500/5'
          : 'border-white/[0.08] bg-white/[0.01] hover:border-white/[0.15] hover:bg-white/[0.02]'
      ]"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        @change="handleFileChange"
      />

      <svg class="h-10 w-10 text-purple-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
      </svg>
      <p class="text-sm font-semibold text-white mb-1">Pilih file atau drag &amp; drop di sini</p>
      <p class="text-xs text-gray-400">
        Maksimal ukuran file: {{ maxSize }}MB. Format yang didukung: {{ accept }}
      </p>
    </div>

    <!-- Selected Files Preview / Progress -->
    <div v-if="selectedFiles.length > 0" class="mt-5 space-y-3">
      <div
        v-for="file in selectedFiles"
        :key="file.id"
        class="border border-white/[0.08] rounded-lg p-4 bg-white/[0.02] flex items-center justify-between gap-4"
      >
        <div class="flex items-center gap-3 overflow-hidden flex-1">
          <svg class="h-8 w-8 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <div class="overflow-hidden flex-1">
            <p class="text-sm text-white font-medium truncate">{{ file.name }}</p>
            <p class="text-xs text-gray-400">{{ formatSize(file.size) }}</p>
            <!-- Real upload progress bar -->
            <div v-if="file.status === 'uploading'" class="mt-1.5 w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
              <div
                class="bg-purple-500 h-full transition-all duration-200"
                :style="{ width: file.progress + '%' }"
              ></div>
            </div>
            <p v-if="file.status === 'error'" class="text-xs text-red-400 mt-1">Gagal diunggah</p>
          </div>
        </div>

        <!-- Status icon / Remove -->
        <div class="flex items-center gap-3 shrink-0">
          <!-- Uploading spinner -->
          <svg v-if="file.status === 'uploading'" class="animate-spin h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <!-- Done checkmark -->
          <span v-else-if="file.status === 'done'" class="text-xs text-emerald-400 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Selesai
          </span>
          <!-- Error icon -->
          <span v-else-if="file.status === 'error'" class="text-xs text-red-400 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Error
          </span>

          <button
            v-if="file.status !== 'uploading'"
            class="text-gray-400 hover:text-white transition-colors"
            @click.stop="removeFile(file.id)"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '../composables/useToast'

const props = defineProps({
  accept: {
    type: String,
    default: '.pdf,.doc,.docx,.jpg,.jpeg,.png'
  },
  maxSize: {
    type: Number,
    default: 10 // 10MB
  },
  multiple: {
    type: Boolean,
    default: false
  },
  uploadUrl: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['upload-complete', 'upload-error'])
const { error } = useToast()

const fileInput = ref(null)
const isDragging = ref(false)
const selectedFiles = ref([])

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileChange = (e) => {
  processFiles(e.target.files)
  // Reset input so the same file can be re-selected after removal
  e.target.value = ''
}

const handleDrop = (e) => {
  isDragging.value = false
  processFiles(e.dataTransfer.files)
}

const processFiles = (filesList) => {
  if (filesList.length === 0) return

  const maxBytes = props.maxSize * 1024 * 1024

  for (let i = 0; i < filesList.length; i++) {
    const file = filesList[i]

    if (file.size > maxBytes) {
      error(`Ukuran file "${file.name}" melebihi batas maksimal ${props.maxSize}MB.`)
      continue
    }

    const fileId = Date.now() + Math.random()
    const fileObj = {
      id: fileId,
      name: file.name,
      size: file.size,
      raw: file,
      progress: 0,
      status: 'uploading' // 'uploading' | 'done' | 'error'
    }

    if (!props.multiple) {
      selectedFiles.value = [fileObj]
    } else {
      selectedFiles.value.push(fileObj)
    }

    uploadFile(fileObj)

    if (!props.multiple) break
  }
}

const uploadFile = (fileObj) => {
  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || ''

  const formData = new FormData()
  formData.append('file', fileObj.raw)

  const xhr = new XMLHttpRequest()

  xhr.upload.addEventListener('progress', (e) => {
    if (e.lengthComputable) {
      const idx = selectedFiles.value.findIndex(f => f.id === fileObj.id)
      if (idx !== -1) {
        // Cap at 95% until server responds; last 5% fills on success
        selectedFiles.value[idx].progress = Math.min(Math.round((e.loaded / e.total) * 95), 95)
      }
    }
  })

  xhr.addEventListener('load', () => {
    const idx = selectedFiles.value.findIndex(f => f.id === fileObj.id)
    if (xhr.status >= 200 && xhr.status < 300) {
      if (idx !== -1) {
        selectedFiles.value[idx].progress = 100
        selectedFiles.value[idx].status = 'done'
      }
      try {
        const data = JSON.parse(xhr.responseText)
        emit('upload-complete', data)
      } catch {
        emit('upload-complete', null)
      }
    } else {
      if (idx !== -1) {
        selectedFiles.value[idx].status = 'error'
      }
      emit('upload-error', fileObj.name)
      error(`Gagal mengunggah file "${fileObj.name}".`)
    }
  })

  xhr.addEventListener('error', () => {
    const idx = selectedFiles.value.findIndex(f => f.id === fileObj.id)
    if (idx !== -1) {
      selectedFiles.value[idx].status = 'error'
    }
    emit('upload-error', fileObj.name)
    error(`Koneksi gagal saat mengunggah "${fileObj.name}".`)
  })

  xhr.open('POST', props.uploadUrl)
  if (token) {
    xhr.setRequestHeader('Authorization', `Bearer ${token}`)
  }
  xhr.setRequestHeader('Accept', 'application/json')
  xhr.send(formData)
}

const removeFile = (id) => {
  selectedFiles.value = selectedFiles.value.filter(f => f.id !== id)
}

const formatSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(2) + ' MB'
}
</script>
