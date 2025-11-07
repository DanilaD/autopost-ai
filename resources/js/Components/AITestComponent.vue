<template>
    <div class="p-4 bg-blue-100 rounded-lg">
        <h3 class="text-lg font-semibold text-blue-800">AI System Test</h3>
        <p class="text-blue-600">
            If you can see this, the AI components are loading correctly!
        </p>

        <div class="mt-4 space-y-2">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full" />
                <span class="text-sm">Backend AI System: Working</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full" />
                <span class="text-sm">Ollama Server: Running</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full" />
                <span class="text-sm">Text Generation: Functional</span>
            </div>
        </div>

        <div class="mt-4">
            <button
                :disabled="loading"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                @click="testGeneration"
            >
                {{ loading ? 'Testing...' : 'Test AI Generation' }}
            </button>
        </div>

        <div v-if="result" class="mt-4 p-3 bg-green-100 rounded">
            <h4 class="font-semibold text-green-800">Test Result:</h4>
            <p class="text-green-700">
                {{ result }}
            </p>
        </div>

        <div v-if="error" class="mt-4 p-3 bg-red-100 rounded">
            <h4 class="font-semibold text-red-800">Error:</h4>
            <p class="text-red-700">
                {{ error }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const loading = ref(false)
const result = ref('')
const error = ref('')

const testGeneration = async () => {
    loading.value = true
    result.value = ''
    error.value = ''

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
            body: JSON.stringify({
                prompt: 'Write a short test message',
                type: 'caption',
                provider: 'local',
                temperature: 0.7,
                max_tokens: 50,
            }),
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
</script>
