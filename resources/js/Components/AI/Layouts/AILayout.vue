<template>
    <AuthenticatedLayout>
        <!-- Header Section -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200"
                    >
                        {{ $t('ai.' + pageTitle) }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $t('ai.' + pageDescription) }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Provider Status Indicator -->
                    <AIProviderStatus :current-provider="currentProvider" />
                    <!-- Quick Actions -->
                    <AIQuickActions :page="pageType" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Error Alert -->
                <AIErrorAlert
                    v-if="error"
                    :error="error"
                    @dismiss="error = ''"
                />

                <!-- Success Alert -->
                <AISuccessAlert
                    v-if="success"
                    :message="success"
                    @dismiss="success = ''"
                />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Input Form -->
                    <div class="lg:col-span-2">
                        <slot name="input" :loading="loading" :form="form" />
                    </div>

                    <!-- Right Column: Results & History -->
                    <div class="lg:col-span-1">
                        <slot
                            name="results"
                            :result="result"
                            :loading="loading"
                        />
                        <slot name="history" :history="history" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AIProviderStatus from '@/Components/AI/Shared/AIProviderStatus.vue'
import AIQuickActions from '@/Components/AI/Shared/AIQuickActions.vue'
import AIErrorAlert from '@/Components/AI/Shared/AIErrorAlert.vue'
import AISuccessAlert from '@/Components/AI/Shared/AISuccessAlert.vue'

const props = defineProps({
    pageTitle: {
        type: String,
        required: true,
    },
    pageDescription: {
        type: String,
        required: true,
    },
    pageType: {
        type: String,
        required: true,
    },
    currentProvider: {
        type: String,
        default: 'local',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    success: {
        type: String,
        default: '',
    },
    form: {
        type: Object,
        default: () => ({}),
    },
    result: {
        type: [String, Object],
        default: null,
    },
    history: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:error', 'update:success'])

// Computed properties for reactive updates
const error = computed({
    get: () => props.error,
    set: (value) => emit('update:error', value),
})

const success = computed({
    get: () => props.success,
    set: (value) => emit('update:success', value),
})
</script>
