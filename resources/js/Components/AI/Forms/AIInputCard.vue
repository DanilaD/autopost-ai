<template>
    <div
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
    >
        <div class="p-6">
            <!-- Form Header -->
            <div class="flex items-center justify-between mb-6">
                <h3
                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ $t('ai.input_title') }}
                </h3>
                <AIProviderSelector
                    :current-provider="form.provider"
                    @provider-change="updateProvider"
                />
            </div>

            <!-- Form Content -->
            <form @submit.prevent="handleSubmit">
                <!-- Primary Input -->
                <div class="mb-6">
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        {{ $t('ai.prompt') }}
                    </label>
                    <textarea
                        v-model="form.prompt"
                        :placeholder="$t('ai.enter_prompt')"
                        :disabled="loading"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        rows="4"
                        required
                    />
                </div>

                <!-- Generation Type Selector -->
                <div class="mb-6">
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        {{ $t('ai.generation_type') }}
                    </label>
                    <select
                        v-model="form.type"
                        :disabled="loading"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <option value="caption">
                            {{ $t('ai.caption') }}
                        </option>
                        <option value="hashtags">
                            {{ $t('ai.hashtags') }}
                        </option>
                        <option value="content_plan">
                            {{ $t('ai.content_plan') }}
                        </option>
                        <option value="general_text">
                            {{ $t('ai.general_text') }}
                        </option>
                    </select>
                </div>

                <!-- Advanced Options (Collapsible) -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                    <button
                        type="button"
                        class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                        @click="showAdvanced = !showAdvanced"
                    >
                        <ChevronDownIcon
                            :class="showAdvanced ? 'rotate-180' : ''"
                            class="w-4 h-4 mr-2 transition-transform"
                        />
                        {{ $t('ai.advanced_options') }}
                    </button>

                    <div v-show="showAdvanced" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Temperature -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    {{ $t('ai.temperature') }}:
                                    {{ form.temperature }}
                                </label>
                                <input
                                    v-model.number="form.temperature"
                                    type="range"
                                    min="0"
                                    max="1"
                                    step="0.1"
                                    :disabled="loading"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                />
                            </div>

                            <!-- Max Tokens -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    {{ $t('ai.max_tokens') }}
                                </label>
                                <input
                                    v-model.number="form.max_tokens"
                                    type="number"
                                    min="50"
                                    max="4000"
                                    :disabled="loading"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6">
                    <button
                        type="button"
                        :disabled="loading"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="resetForm"
                    >
                        <ArrowPathIcon class="w-4 h-4 mr-2" />
                        {{ $t('ai.clear') }}
                    </button>

                    <button
                        type="submit"
                        :disabled="loading || !form.prompt.trim()"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <div v-if="loading" class="flex items-center">
                            <div
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                            />
                            {{ $t('ai.generating') }}
                        </div>
                        <div v-else class="flex items-center">
                            <SparklesIcon class="w-4 h-4 mr-2" />
                            {{ $t('ai.generate') }}
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
    ChevronDownIcon,
    ArrowPathIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline'
import AIProviderSelector from './AIProviderSelector.vue'

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    initialForm: {
        type: Object,
        default: () => ({}),
    },
})

const emit = defineEmits(['submit', 'provider-change'])

const showAdvanced = ref(false)

const form = reactive({
    prompt: '',
    type: 'caption',
    provider: 'local',
    temperature: 0.7,
    max_tokens: 1000,
    ...props.initialForm,
})

const handleSubmit = () => {
    if (!form.prompt.trim() || props.loading) return

    emit('submit', { ...form })
}

const resetForm = () => {
    form.prompt = ''
    form.type = 'caption'
    form.temperature = 0.7
    form.max_tokens = 1000
}

const updateProvider = (provider) => {
    form.provider = provider
    emit('provider-change', provider)
}
</script>
