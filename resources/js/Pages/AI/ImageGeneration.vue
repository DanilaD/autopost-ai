<template>
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-pattern-neutral-900 dark:text-pattern-neutral-100 leading-tight"
            >
                {{ $t('ai.image_generation') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Image Generation Form -->
                <div class="bg-glass-card shadow-glass-md mb-8">
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 mb-4"
                        >
                            {{ $t('ai.image_generation') }}
                        </h3>

                        <form class="space-y-6" @submit.prevent="generateImage">
                            <!-- Prompt Input -->
                            <div>
                                <label
                                    for="prompt"
                                    class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 mb-2"
                                >
                                    {{ $t('common.prompt') || 'Prompt' }}
                                </label>
                                <textarea
                                    id="prompt"
                                    v-model="form.prompt"
                                    rows="3"
                                    class="input-glass w-full"
                                    :placeholder="
                                        $t('ai.describe_image') ||
                                        'Describe the image you want to generate...'
                                    "
                                    required
                                />
                            </div>

                            <!-- Image Size -->
                            <div>
                                <label
                                    for="size"
                                    class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 mb-2"
                                >
                                    {{ $t('ai.image_size') || 'Image Size' }}
                                </label>
                                <select
                                    id="size"
                                    v-model="form.size"
                                    class="input-glass w-full"
                                >
                                    <option value="1024x1024">
                                        1024x1024 (Square)
                                    </option>
                                    <option value="1024x1792">
                                        1024x1792 (Portrait)
                                    </option>
                                    <option value="1792x1024">
                                        1792x1024 (Landscape)
                                    </option>
                                </select>
                            </div>

                            <!-- Quality -->
                            <div>
                                <label
                                    for="quality"
                                    class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 mb-2"
                                >
                                    {{ $t('ai.quality') || 'Quality' }}
                                </label>
                                <select
                                    id="quality"
                                    v-model="form.quality"
                                    class="input-glass w-full"
                                >
                                    <option value="standard">
                                        {{ $t('ai.standard') || 'Standard' }}
                                    </option>
                                    <option value="hd">
                                        {{ $t('ai.hd') || 'HD' }}
                                    </option>
                                </select>
                            </div>

                            <!-- Provider Selection -->
                            <div>
                                <label
                                    for="provider"
                                    class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300 mb-2"
                                >
                                    {{ $t('ai.provider') || 'AI Provider' }}
                                </label>
                                <select
                                    id="provider"
                                    v-model="form.provider"
                                    class="input-glass w-full"
                                >
                                    <option value="auto">
                                        {{
                                            $t('ai.auto_select') ||
                                            'Auto Select (Recommended)'
                                        }}
                                    </option>
                                    <option
                                        v-for="(provider, name) in providers"
                                        :key="name"
                                        :value="name"
                                        :disabled="!provider.available"
                                    >
                                        {{ name }}
                                        {{
                                            !provider.available
                                                ? '(Unavailable)'
                                                : ''
                                        }}
                                    </option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="btn-glass-success"
                                >
                                    <span v-if="loading" class="mr-2">
                                        <svg
                                            class="animate-spin h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            />
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            />
                                        </svg>
                                    </span>
                                    {{
                                        loading
                                            ? $t('ai.generating') ||
                                              'Generating...'
                                            : $t('ai.generate') || 'Generate'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.results') || 'Generation Results' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- Results -->
                <div v-if="result" class="bg-glass-card shadow-glass-md">
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 mb-4"
                        >
                            {{ $t('ai.generated_image') || 'Generated Image' }}
                        </h3>

                        <div class="flex justify-center mb-4">
                            <img
                                :src="result.url"
                                :alt="form.prompt"
                                class="max-w-full h-auto rounded-lg shadow-lg"
                                @error="handleImageError"
                            />
                        </div>

                        <!-- Image Info -->
                        <div class="bg-glass-soft p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span
                                        class="font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                        >{{
                                            $t('ai.provider') || 'Provider'
                                        }}:</span
                                    >
                                    <span
                                        class="ml-2 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                        >{{ result.provider }}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                        >{{ $t('ai.cost') || 'Cost' }}:</span
                                    >
                                    <span
                                        class="ml-2 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                        >${{
                                            result.cost_usd?.toFixed(4) ||
                                            '0.0000'
                                        }}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                        >{{ $t('ai.size') || 'Size' }}:</span
                                    >
                                    <span
                                        class="ml-2 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                        >{{ form.size }}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                        >{{
                                            $t('ai.quality') || 'Quality'
                                        }}:</span
                                    >
                                    <span
                                        class="ml-2 text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                        >{{ form.quality }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Result Actions -->
                        <div class="flex justify-end space-x-2">
                            <button
                                class="btn-glass-secondary"
                                @click="downloadImage"
                            >
                                {{ $t('ai.download') || 'Download' }}
                            </button>
                            <button
                                class="btn-glass-secondary"
                                @click="clearResult"
                            >
                                {{ $t('ai.clear') || 'Clear' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Display -->
                <div
                    v-if="error"
                    class="bg-pattern-error-container border border-pattern-error rounded-md p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-pattern-error"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3
                                class="text-sm font-medium text-pattern-on-error-container"
                            >
                                {{ $t('common.error') || 'Error' }}
                            </h3>
                            <div
                                class="mt-2 text-sm text-pattern-on-error-container"
                            >
                                {{ error }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    providers: Object,
})

const form = reactive({
    prompt: '',
    size: '1024x1024',
    quality: 'standard',
    provider: 'auto',
})

const result = ref(null)
const error = ref('')
const loading = ref(false)

const generateImage = async () => {
    loading.value = true
    error.value = ''
    result.value = null

    try {
        // Get CSRF token from meta tag
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content')

        const response = await fetch('/ai/test/image', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(form),
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
            success.value = 'Image generated successfully!'
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
                            'Please enter a description for the image you want to generate.'
                    } else if (field === 'size') {
                        errorMessage = 'Please select a valid image size.'
                    } else if (field === 'quality') {
                        errorMessage =
                            'Please select a valid image quality setting.'
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
                        'Your account has insufficient credits. Please add credits to your account or try a different provider.'
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

const downloadImage = () => {
    if (result.value?.url) {
        const link = document.createElement('a')
        link.href = result.value.url
        link.download = `ai-generated-${Date.now()}.png`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
    }
}

const clearResult = () => {
    result.value = null
    error.value = ''
}

const handleImageError = () => {
    error.value = 'Failed to load generated image'
}
</script>
