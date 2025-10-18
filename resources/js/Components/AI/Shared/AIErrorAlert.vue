<template>
    <div v-if="error" class="mb-6">
        <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
            <div class="flex">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                <div class="ml-3 flex-1">
                    <h3
                        class="text-sm font-medium text-red-800 dark:text-red-200"
                    >
                        {{ $t('ai.error_title') }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        {{ error }}
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <button
                        class="inline-flex rounded-md bg-red-50 dark:bg-red-900/20 p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50 dark:focus:ring-offset-red-900/20"
                        @click="$emit('dismiss')"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
    error: {
        type: String,
        required: true,
    },
})

defineEmits(['dismiss'])
</script>
