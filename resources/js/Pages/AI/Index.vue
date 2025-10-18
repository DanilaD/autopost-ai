<template>
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-pattern-neutral-900 dark:text-pattern-neutral-100 leading-tight"
            >
                {{ $t('ai.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Translation Debug Component - Temporarily disabled -->
                <!-- <div class="mb-8">
      <TranslationDebug />
    </div> -->

                <!-- AI Test Component -->
                <div class="mb-8">
                    <AITestComponent />
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.overview') || 'AI Overview' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- AI Overview Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <!-- Available Providers -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-pattern-success"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ $t('ai.available_providers') }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ Object.keys(providers).length }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Usage -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-pattern-info"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ $t('ai.total_usage') }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ usageStats.total_generations || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cost -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-pattern-warning"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ $t('ai.total_cost') }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        ${{
                                            (
                                                usageStats.total_cost || 0
                                            ).toFixed(4)
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Models -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-pattern-accent"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ $t('ai.active_models') }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ getTotalModels() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.features') || 'AI Features' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- AI Features Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
                >
                    <!-- Text Generation -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3
                                        class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ $t('ai.text_generation') }}
                                    </h3>
                                    <p
                                        class="text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400 mt-1"
                                    >
                                        {{ $t('ai.text_generation_desc') }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('ai.text')"
                                    class="btn-glass-primary"
                                >
                                    {{ $t('ai.start') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Image Generation -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3
                                        class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ $t('ai.image_generation') }}
                                    </h3>
                                    <p
                                        class="text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400 mt-1"
                                    >
                                        {{ $t('ai.image_generation_desc') }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('ai.image')"
                                    class="btn-glass-success"
                                >
                                    {{ $t('ai.start') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- AI Chat -->
                    <div class="bg-glass-card shadow-glass-md">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3
                                        class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ $t('ai.ai_chat') }}
                                    </h3>
                                    <p
                                        class="text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400 mt-1"
                                    >
                                        {{ $t('ai.ai_chat_desc') }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('ai.chat')"
                                    class="btn-glass-accent"
                                >
                                    {{ $t('ai.start') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.status') || 'Provider Status' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- Provider Status -->
                <div class="bg-glass-card shadow-glass-md mb-8">
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 mb-4"
                        >
                            {{ $t('ai.provider_status') }}
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                        >
                            <div
                                v-for="(provider, name) in providers"
                                :key="name"
                                class="bg-glass-soft border border-glass-border-soft rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4
                                            class="font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 capitalize"
                                        >
                                            {{ name }}
                                        </h4>
                                        <p
                                            class="text-sm text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                        >
                                            {{
                                                getProviderModelCount(provider)
                                            }}
                                            {{ $t('ai.models') }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span
                                            :class="
                                                provider.available
                                                    ? 'bg-pattern-success text-pattern-on-success'
                                                    : 'bg-pattern-error text-pattern-on-error'
                                            "
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{
                                                provider.available
                                                    ? $t('ai.available')
                                                    : $t('ai.unavailable')
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.activity') || 'Recent Activity' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- Recent Activity -->
                <div class="bg-glass-card shadow-glass-md mb-8">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3
                                class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100"
                            >
                                {{ $t('ai.recent_activity') }}
                            </h3>
                            <Link
                                :href="route('ai.analytics')"
                                class="text-sm text-pattern-primary hover:text-pattern-primary-dark"
                            >
                                {{ $t('ai.view_all') }}
                            </Link>
                        </div>
                        <div
                            v-if="recentGenerations.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="generation in recentGenerations.slice(
                                    0,
                                    5
                                )"
                                :key="generation.id"
                                class="flex items-center justify-between p-3 bg-glass-soft rounded-lg"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        {{ generation.type }}
                                    </p>
                                    <p
                                        class="text-xs text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ generation.provider }} •
                                        {{ formatDate(generation.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm text-pattern-neutral-900 dark:text-pattern-neutral-100"
                                    >
                                        ${{
                                            generation.cost_usd?.toFixed(4) ||
                                            '0.0000'
                                        }}
                                    </p>
                                    <p
                                        class="text-xs text-pattern-neutral-600 dark:text-pattern-neutral-400"
                                    >
                                        {{ generation.tokens_used || 0 }}
                                        {{ $t('tokens') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p
                                class="text-pattern-neutral-600 dark:text-pattern-neutral-400"
                            >
                                {{ $t('ai.no_recent_activity') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section Separator -->
                <div class="flex items-center my-8">
                    <div class="flex-1 border-t border-glass-border-soft" />
                    <div class="px-4">
                        <h3
                            class="text-sm font-medium text-pattern-neutral-600 dark:text-pattern-neutral-400 uppercase tracking-wider"
                        >
                            {{ $t('ai.actions') || 'Quick Actions' }}
                        </h3>
                    </div>
                    <div class="flex-1 border-t border-glass-border-soft" />
                </div>

                <!-- Quick Actions -->
                <div class="bg-glass-card shadow-glass-md">
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-pattern-neutral-900 dark:text-pattern-neutral-100 mb-4"
                        >
                            {{ $t('ai.quick_actions') }}
                        </h3>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <Link
                                :href="route('ai.test.providers')"
                                class="btn-glass-secondary"
                            >
                                {{ $t('ai.test_providers') }}
                            </Link>
                            <Link
                                :href="route('ai.text')"
                                class="btn-glass-secondary"
                            >
                                {{ $t('ai.text_generation') }}
                            </Link>
                            <Link
                                :href="route('ai.image')"
                                class="btn-glass-secondary"
                            >
                                {{ $t('ai.image_generation') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AITestComponent from '@/Components/AITestComponent.vue'
// import TranslationDebug from '@/Components/TranslationDebug.vue'

const props = defineProps({
    providers: Object,
    providerStats: Object,
    usageStats: Object,
    recentGenerations: Array,
    costComparison: Object,
    fallbackChain: Array,
})

const getTotalModels = () => {
    let total = 0
    Object.values(props.providers).forEach((provider) => {
        if (provider.models) {
            total +=
                (provider.models.text?.length || 0) +
                (provider.models.image?.length || 0)
        }
    })
    return total
}

const getProviderModelCount = (provider) => {
    if (!provider.models) return 0
    return (
        (provider.models.text?.length || 0) +
        (provider.models.image?.length || 0)
    )
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}
</script>
