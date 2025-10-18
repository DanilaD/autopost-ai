<template>
    <div v-if="message" class="mb-6">
        <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
            <div class="flex">
                <CheckCircleIcon class="h-5 w-5 text-green-400" />
                <div class="ml-3 flex-1">
                    <h3
                        class="text-sm font-medium text-green-800 dark:text-green-200"
                    >
                        {{ $t('ai.success_title') }}
                    </h3>
                    <div
                        class="mt-2 text-sm text-green-700 dark:text-green-300"
                    >
                        {{ message }}
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <button
                        class="inline-flex rounded-md bg-green-50 dark:bg-green-900/20 p-1.5 text-green-500 hover:bg-green-100 dark:hover:bg-green-900/30 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50 dark:focus:ring-offset-green-900/20"
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
import { CheckCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
    message: {
        type: String,
        required: true,
    },
})

defineEmits(['dismiss'])
</script>
