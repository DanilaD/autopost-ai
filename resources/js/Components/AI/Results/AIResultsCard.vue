<template>
    <div
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
    >
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3
                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ $t('ai.result') }}
                </h3>
                <div class="flex items-center space-x-2">
                    <AICostIndicator v-if="result?.cost" :cost="result.cost" />
                    <AIProviderBadge :provider="result?.provider" />
                </div>
            </div>

            <!-- Results Content -->
            <div v-if="result" class="space-y-4">
                <!-- Main Result -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="prose dark:prose-invert max-w-none">
                        <div
                            v-if="typeof result === 'string'"
                            v-html="formatResult(result)"
                        />
                        <div
                            v-else-if="result.content"
                            v-html="formatResult(result.content)"
                        />
                        <div
                            v-else-if="result.result"
                            v-html="formatResult(result.result)"
                        />
                    </div>
                </div>

                <!-- Metadata -->
                <div
                    v-if="result.metadata"
                    class="text-xs text-gray-500 dark:text-gray-400"
                >
                    <div class="flex items-center justify-between">
                        <span
                            >{{ $t('ai.model') }}:
                            {{ result.model || result.metadata.model }}</span
                        >
                        <span v-if="result.tokens"
                            >{{ $t('ai.tokens') }}: {{ result.tokens }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-8">
                <SparklesIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3
                    class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ $t('ai.no_result') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('ai.generate_content_first') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div
                v-if="result"
                class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-600"
            >
                <button
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    @click="copyResult"
                >
                    <ClipboardIcon class="w-4 h-4 mr-2" />
                    {{ $t('ai.copy') }}
                </button>

                <div class="flex items-center space-x-2">
                    <button
                        :disabled="loading"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="$emit('regenerate')"
                    >
                        <ArrowPathIcon class="w-4 h-4 mr-2" />
                        {{ $t('ai.regenerate') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    SparklesIcon,
    ClipboardIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import AICostIndicator from './AICostIndicator.vue'
import AIProviderBadge from './AIProviderBadge.vue'

const props = defineProps({
    result: {
        type: [String, Object],
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['regenerate'])

const formatResult = (text) => {
    if (!text) return ''

    // Convert line breaks to HTML
    return text.replace(/\n/g, '<br>')
}

const copyResult = async () => {
    const text =
        typeof props.result === 'string'
            ? props.result
            : props.result?.content || props.result?.result || ''

    try {
        await navigator.clipboard.writeText(text)
        // You could emit a success event here
    } catch (error) {
        console.error('Failed to copy text:', error)
    }
}
</script>
