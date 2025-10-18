<template>
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-pattern-neutral-900 dark:text-pattern-neutral-100 leading-tight"
            >
                {{ $t('ai.ai_chat') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Chat Interface -->
                <div class="bg-glass-card shadow-glass-md">
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 mb-4"
                        >
                            {{ $t('ai.ai_chat') }}
                        </h3>

                        <!-- Chat Messages -->
                        <div
                            class="h-96 overflow-y-auto border border-glass-border-soft rounded-lg p-4 mb-4 bg-glass-soft"
                        >
                            <div
                                v-if="messages.length === 0"
                                class="text-center text-pattern-neutral-500 dark:text-pattern-neutral-500 py-8"
                            >
                                {{
                                    $t('ai.start_conversation') ||
                                    'Start a conversation with AI...'
                                }}
                            </div>

                            <div
                                v-for="(message, index) in messages"
                                :key="index"
                                class="mb-4"
                            >
                                <div
                                    :class="
                                        message.role === 'user'
                                            ? 'flex justify-end'
                                            : 'flex justify-start'
                                    "
                                >
                                    <div
                                        :class="
                                            message.role === 'user'
                                                ? 'bg-pattern-primary text-pattern-on-primary'
                                                : 'bg-glass-soft text-pattern-neutral-900 dark:text-pattern-neutral-100'
                                        "
                                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                                    >
                                        <div class="text-sm font-medium mb-1">
                                            {{
                                                message.role === 'user'
                                                    ? $t('ai.you') || 'You'
                                                    : $t('ai.assistant') ||
                                                      'AI Assistant'
                                            }}
                                        </div>
                                        <div class="whitespace-pre-wrap">
                                            {{ message.content }}
                                        </div>
                                        <div class="text-xs opacity-75 mt-1">
                                            {{ formatTime(message.timestamp) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading indicator -->
                            <div v-if="loading" class="flex justify-start">
                                <div
                                    class="bg-glass-soft text-pattern-neutral-900 dark:text-pattern-neutral-100 max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                                >
                                    <div class="flex items-center space-x-2">
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
                                        <span>{{
                                            $t('ai.thinking') ||
                                            'AI is thinking...'
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Separator -->
                        <div class="flex items-center my-6">
                            <div
                                class="flex-1 border-t border-glass-border-soft"
                            />
                            <div class="px-4">
                                <h4
                                    class="text-xs font-medium text-pattern-neutral-500 dark:text-pattern-neutral-500 uppercase tracking-wider"
                                >
                                    {{ $t('ai.chat_input') || 'Chat Input' }}
                                </h4>
                            </div>
                            <div
                                class="flex-1 border-t border-glass-border-soft"
                            />
                        </div>

                        <!-- Chat Input -->
                        <form
                            class="flex space-x-2"
                            @submit.prevent="sendMessage"
                        >
                            <div class="flex-1">
                                <textarea
                                    v-model="newMessage"
                                    rows="2"
                                    class="input-glass w-full resize-none"
                                    :placeholder="
                                        $t('ai.type_message') ||
                                        'Type your message...'
                                    "
                                    :disabled="loading"
                                    @keydown.enter.prevent="sendMessage"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="loading || !newMessage.trim()"
                                class="btn-glass-accent"
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
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                    />
                                </svg>
                            </button>
                        </form>

                        <!-- Section Separator -->
                        <div class="flex items-center my-6">
                            <div
                                class="flex-1 border-t border-glass-border-soft"
                            />
                            <div class="px-4">
                                <h4
                                    class="text-xs font-medium text-pattern-neutral-500 dark:text-pattern-neutral-500 uppercase tracking-wider"
                                >
                                    {{ $t('ai.settings') || 'Settings' }}
                                </h4>
                            </div>
                            <div
                                class="flex-1 border-t border-glass-border-soft"
                            />
                        </div>

                        <!-- Chat Settings -->
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <label
                                        for="provider"
                                        class="block text-sm font-medium text-pattern-neutral-700 dark:text-pattern-neutral-300"
                                    >
                                        {{ $t('ai.provider') || 'AI Provider' }}
                                    </label>
                                    <select
                                        id="provider"
                                        v-model="chatProvider"
                                        class="input-glass text-sm mt-1"
                                    >
                                        <option value="auto">
                                            {{
                                                $t('ai.auto_select') ||
                                                'Auto Select'
                                            }}
                                        </option>
                                        <option
                                            v-for="(
                                                provider, name
                                            ) in providers"
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
                            </div>

                            <button
                                class="btn-glass-secondary"
                                @click="clearChat"
                            >
                                {{ $t('ai.clear_chat') || 'Clear Chat' }}
                            </button>
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

const messages = ref([])
const newMessage = ref('')
const chatProvider = ref('auto')
const loading = ref(false)

const sendMessage = async () => {
    if (!newMessage.value.trim() || loading.value) return

    const userMessage = {
        role: 'user',
        content: newMessage.value.trim(),
        timestamp: new Date(),
    }

    messages.value.push(userMessage)
    const currentMessage = newMessage.value.trim()
    newMessage.value = ''
    loading.value = true

    try {
        // Get CSRF token from meta tag
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content')

        const response = await fetch('/ai/test/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: currentMessage,
                provider: chatProvider.value,
            }),
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        if (data.success && data.data) {
            const assistantMessage = {
                role: 'assistant',
                content: data.data.content || data.data.response || data.data,
                timestamp: new Date(),
            }
            messages.value.push(assistantMessage)
        } else {
            const errorMessage = {
                role: 'assistant',
                content: `Error: ${data.message || 'Failed to send message'}`,
                timestamp: new Date(),
            }
            messages.value.push(errorMessage)
        }

        loading.value = false
    } catch (err) {
        const errorMessage = {
            role: 'assistant',
            content: `Error: ${err.message}`,
            timestamp: new Date(),
        }
        messages.value.push(errorMessage)
        loading.value = false
    }
}

const clearChat = () => {
    messages.value = []
}

const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString()
}
</script>
