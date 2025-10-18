<template>
    <AILayout
        page-title="text_generation"
        page-description="text_generation_desc"
        page-type="text"
        :current-provider="currentProvider"
        :loading="loading"
        :error="error"
        :success="success"
        :form="form"
        :result="result"
        @update:error="error = $event"
        @update:success="success = $event"
    >
        <!-- Input Slot -->
        <template #input="{ loading, form }">
            <!-- Temporarily disabled AIInputCard to fix translation warnings -->
            <div class="bg-glass-card shadow-glass-md">
                <div
                    class="p-6 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    <h3 class="text-lg font-semibold mb-4">
                        Text Generation Test
                    </h3>
                    <form
                        class="space-y-4"
                        @submit.prevent="generateText(form)"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                >Prompt</label
                            >
                            <input
                                v-model="form.prompt"
                                type="text"
                                class="input-glass w-full mt-1"
                                placeholder="Enter your prompt here..."
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                >Provider</label
                            >
                            <select
                                v-model="form.provider"
                                class="input-glass w-full mt-1"
                            >
                                <option value="local">Local AI</option>
                                <option value="openai">OpenAI</option>
                                <option value="anthropic">Anthropic</option>
                                <option value="google">Google AI</option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="btn-glass-primary w-full"
                        >
                            {{ loading ? 'Generating...' : 'Generate Text' }}
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Section Separator -->
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-glass-border-soft" />
            <div class="px-4">
                <h4
                    class="text-xs font-medium text-pattern-neutral-500 dark:text-pattern-neutral-500 uppercase tracking-wider"
                >
                    {{ $t('ai.results') || 'Generation Results' }}
                </h4>
            </div>
            <div class="flex-1 border-t border-glass-border-soft" />
        </div>

        <!-- Results Slot -->
        <template #results="{ result, loading }">
            <!-- Temporarily disabled AIResultsCard to fix translation warnings -->
            <div class="bg-glass-card shadow-glass-md">
                <div
                    class="p-6 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    <h3 class="text-lg font-semibold mb-4">
                        Generation Result
                    </h3>
                    <div
                        v-if="loading"
                        class="text-pattern-neutral-600 dark:text-pattern-neutral-400"
                    >
                        Generating...
                    </div>
                    <div v-else-if="result" class="whitespace-pre-wrap">
                        {{ result }}
                    </div>
                    <div
                        v-else
                        class="text-pattern-neutral-500 dark:text-pattern-neutral-500"
                    >
                        No result yet
                    </div>
                </div>
            </div>
        </template>

        <!-- Section Separator -->
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-glass-border-soft" />
            <div class="px-4">
                <h4
                    class="text-xs font-medium text-pattern-neutral-500 dark:text-pattern-neutral-500 uppercase tracking-wider"
                >
                    {{ $t('ai.history') || 'Generation History' }}
                </h4>
            </div>
            <div class="flex-1 border-t border-glass-border-soft" />
        </div>

        <!-- History Slot -->
        <template #history>
            <!-- Temporarily disabled AIHistoryCard to fix translation warnings -->
            <div class="bg-glass-card shadow-glass-md">
                <div
                    class="p-6 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                >
                    <h3 class="text-lg font-semibold mb-4">
                        Recent Generations
                    </h3>
                    <div
                        class="text-pattern-neutral-500 dark:text-pattern-neutral-500"
                    >
                        No recent generations
                    </div>
                </div>
            </div>
        </template>
    </AILayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AILayout from '@/Components/AI/Layouts/AILayout.vue'
// import AIInputCard from '@/Components/AI/Forms/AIInputCard.vue'
// import AIResultsCard from '@/Components/AI/Results/AIResultsCard.vue'
// import AIHistoryCard from '@/Components/AI/Results/AIHistoryCard.vue'

const props = defineProps({
    providers: Object,
})

const loading = ref(false)
const error = ref('')
const success = ref('')
const result = ref(null)
const currentProvider = ref('local')

const form = reactive({
    prompt: '',
    type: 'caption',
    provider: 'local',
    temperature: 0.7,
    max_tokens: 1000,
})

const generateText = async (formData) => {
    loading.value = true
    error.value = ''
    success.value = ''
    result.value = null

    try {
        // Get CSRF token from meta tag
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content')

        const response = await fetch('/ai/test/text-generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData),
        })

        if (!response.ok) {
            if (response.status === 419) {
                error.value = 'CSRF token mismatch. Please refresh the page.'
            } else if (response.status === 401) {
                error.value = 'Authentication required. Please log in.'
            } else {
                // For 400 and other errors, try to parse the JSON response for a better error message
                try {
                    const errorData = await response.json()
                    if (errorData.message) {
                        error.value = errorData.message
                    } else {
                        error.value = `Request failed: ${response.status} ${response.statusText}`
                    }
                } catch (parseError) {
                    error.value = `Request failed: ${response.status} ${response.statusText}`
                }
            }
            loading.value = false
            return
        }

        const data = await response.json()

        if (data.success && data.data) {
            // Extract the actual AI generation result
            result.value = data.data.content || data.data.result || data.data
            success.value = 'Text generated successfully!'
        } else {
            // Show user-friendly error messages
            let errorMessage = data.message || 'Generation failed'

            if (data.errors) {
                // Handle validation errors with user-friendly messages
                const errorKeys = Object.keys(data.errors)
                if (errorKeys.length > 0) {
                    const field = errorKeys[0]
                    if (field === 'provider') {
                        errorMessage =
                            'Please select a valid AI provider from the dropdown.'
                    } else if (field === 'prompt') {
                        errorMessage =
                            'Please enter a prompt for text generation.'
                    } else if (field === 'temperature') {
                        errorMessage =
                            'Please enter a valid temperature value (0-2).'
                    } else if (field === 'max_tokens') {
                        errorMessage =
                            'Please enter a valid number of tokens (1-4000).'
                    } else {
                        errorMessage = 'Please check your input and try again.'
                    }
                }
            } else if (data.data && data.data.error) {
                const errorText = data.data.error.toLowerCase()

                if (errorText.includes('billing hard limit has been reached')) {
                    errorMessage =
                        'Your OpenAI account has reached its billing limit. Please add credits to your account or try a different provider.'
                } else if (errorText.includes('credit balance is too low')) {
                    errorMessage =
                        'Your Anthropic account has insufficient credits. Please add credits to your account or try a different provider.'
                } else if (errorText.includes('quota')) {
                    errorMessage =
                        'API quota exceeded. Please try again later or use a different provider.'
                } else if (errorText.includes('invalid_request_error')) {
                    errorMessage =
                        'Invalid request. Please check your input and try again.'
                } else if (errorText.includes('authentication')) {
                    errorMessage =
                        'Authentication failed. Please check your API keys.'
                } else if (errorText.includes('rate limit')) {
                    errorMessage =
                        'Rate limit exceeded. Please wait a moment and try again.'
                } else if (errorText.includes('model not found')) {
                    errorMessage =
                        'The selected AI model is not available. Please try a different model or provider.'
                } else {
                    // Extract the actual error message from the technical error
                    const match = errorText.match(
                        /message[":\s]*["']([^"']+)["']/
                    )
                    if (match && match[1]) {
                        errorMessage = match[1]
                    }
                }
            }

            error.value = errorMessage
        }

        loading.value = false
    } catch (err) {
        error.value = err.message
        loading.value = false
    }
}

const regenerateText = () => {
    if (form.prompt.trim()) {
        generateText(form)
    }
}

const updateProvider = (provider) => {
    currentProvider.value = provider
    form.provider = provider
}
</script>
