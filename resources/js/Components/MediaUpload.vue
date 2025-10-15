<template>
    <div class="space-y-4">
        <!-- Upload Area -->
        <div
            class="relative rounded-md border-2 border-dashed border-gray-300 p-6 text-center hover:border-gray-400 dark:border-gray-600"
            :class="{
                'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20':
                    isDragOver,
            }"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @drop.prevent="handleDrop"
        >
            <input
                ref="fileInput"
                type="file"
                :multiple="maxFiles > 1"
                :accept="acceptedTypes"
                class="hidden"
                @change="handleFileSelect"
            />

            <div v-if="modelValue.length === 0" class="space-y-2">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                    stroke="currentColor"
                    fill="none"
                    viewBox="0 0 48 48"
                >
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <button
                        type="button"
                        class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                        @click="$refs.fileInput.click()"
                    >
                        {{ t('posts.media.upload') }}
                    </button>
                    {{ t('posts.media.or_drag_drop') }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ t('posts.media.supported_formats') }}
                </p>
            </div>

            <div v-else class="space-y-2">
                <button
                    type="button"
                    class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    @click="$refs.fileInput.click()"
                >
                    {{ t('posts.media.add_more') }}
                </button>
            </div>
        </div>

        <!-- Media Preview Grid -->
        <div
            v-if="modelValue.length > 0"
            class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"
        >
            <div
                v-for="(media, index) in modelValue"
                v-show="!media.markedForDeletion"
                :key="index"
                class="group relative aspect-square overflow-hidden rounded-md bg-gray-100 dark:bg-gray-700"
            >
                <!-- Image Preview -->
                <img
                    v-if="media.type === 'image'"
                    :src="media.preview"
                    :alt="media.name"
                    class="h-full w-full object-cover"
                />

                <!-- Video Preview -->
                <video
                    v-else
                    :src="media.preview"
                    class="h-full w-full object-cover"
                    muted
                />

                <!-- Existing Media Badge -->
                <div v-if="media.isExisting" class="absolute top-2 left-2">
                    <span
                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                        :class="
                            media.isDuplicated
                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        "
                    >
                        {{
                            media.isDuplicated
                                ? t('posts.media.duplicated')
                                : t('posts.media.existing')
                        }}
                    </span>
                </div>

                <!-- Overlay -->
                <div
                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200"
                >
                    <div
                        class="flex h-full items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                    >
                        <div class="flex space-x-2">
                            <button
                                type="button"
                                class="rounded-full bg-white p-2 text-gray-600 hover:text-gray-900"
                                @click="removeMedia(index)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>

                            <button
                                v-if="index > 0"
                                type="button"
                                class="rounded-full bg-white p-2 text-gray-600 hover:text-gray-900"
                                @click="moveMedia(index, index - 1)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 15l7-7 7 7"
                                    />
                                </svg>
                            </button>

                            <button
                                v-if="index < modelValue.length - 1"
                                type="button"
                                class="rounded-full bg-white p-2 text-gray-600 hover:text-gray-900"
                                @click="moveMedia(index, index + 1)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Media Info -->
                <div
                    class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 p-2 text-white"
                >
                    <p class="truncate text-xs">
                        {{ media.name }}
                    </p>
                    <p class="text-xs text-gray-300">
                        {{ formatFileSize(media.size) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="uploading" class="space-y-2">
            <div
                class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400"
            >
                <span>{{ t('posts.media.uploading') }}</span>
                <span>{{ uploadProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div
                    class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: uploadProgress + '%' }"
                />
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errors.length > 0" class="space-y-1">
            <p
                v-for="error in errors"
                :key="error"
                class="text-sm text-red-600 dark:text-red-400"
            >
                {{ error }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    maxFiles: {
        type: Number,
        default: 10,
    },
    allowedTypes: {
        type: Array,
        default: () => ['image', 'video'],
    },
    maxFileSize: {
        type: Number,
        default: 100 * 1024 * 1024, // 100MB
    },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const isDragOver = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const errors = ref([])

const acceptedTypes = computed(() => {
    const mimeTypes = []
    if (props.allowedTypes.includes('image')) {
        mimeTypes.push('image/jpeg', 'image/png', 'image/gif', 'image/webp')
    }
    if (props.allowedTypes.includes('video')) {
        mimeTypes.push('video/mp4', 'video/quicktime', 'video/x-msvideo')
    }
    return mimeTypes.join(',')
})

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files)
    processFiles(files)
    event.target.value = '' // Reset input
}

const handleDrop = (event) => {
    isDragOver.value = false
    const files = Array.from(event.dataTransfer.files)
    processFiles(files)
}

const processFiles = (files) => {
    errors.value = []

    // Check file count
    if (props.modelValue.length + files.length > props.maxFiles) {
        errors.value.push(
            t('posts.media.too_many_files', { max: props.maxFiles })
        )
        return
    }

    const newMedia = []

    files.forEach((file) => {
        // Validate file size
        if (file.size > props.maxFileSize) {
            errors.value.push(
                t('posts.media.file_too_large', {
                    name: file.name,
                    max: formatFileSize(props.maxFileSize),
                })
            )
            return
        }

        // Validate file type
        const fileType = file.type.startsWith('image/') ? 'image' : 'video'
        if (!props.allowedTypes.includes(fileType)) {
            errors.value.push(
                t('posts.media.invalid_file_type', {
                    name: file.name,
                    type: fileType,
                })
            )
            return
        }

        // Create media object
        const media = {
            file: file,
            name: file.name,
            type: fileType,
            size: file.size,
            preview: URL.createObjectURL(file),
        }

        newMedia.push(media)
    })

    if (newMedia.length > 0) {
        emit('update:modelValue', [...props.modelValue, ...newMedia])
    }
}

const removeMedia = (index) => {
    const newMedia = [...props.modelValue]
    const media = newMedia[index]

    // If it's an existing media, mark it for deletion instead of removing
    if (media.isExisting) {
        media.markedForDeletion = true
        media.preview = null // Hide the preview
    } else {
        // For new files, revoke the object URL and remove from array
        if (media.preview && media.preview.startsWith('blob:')) {
            URL.revokeObjectURL(media.preview)
        }
        newMedia.splice(index, 1)
    }

    emit('update:modelValue', newMedia)
}

const moveMedia = (fromIndex, toIndex) => {
    const newMedia = [...props.modelValue]
    const [movedMedia] = newMedia.splice(fromIndex, 1)
    newMedia.splice(toIndex, 0, movedMedia)
    emit('update:modelValue', newMedia)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Clean up object URLs when component is unmounted
watch(
    () => props.modelValue,
    (newValue, oldValue) => {
        if (oldValue) {
            oldValue.forEach((media) => {
                if (media.preview && media.preview.startsWith('blob:')) {
                    URL.revokeObjectURL(media.preview)
                }
            })
        }
    },
    { deep: true }
)
</script>
