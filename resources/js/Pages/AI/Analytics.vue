<template>
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                {{ $t('ai.analytics') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Analytics Overview -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <!-- Total Generations -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-blue-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            $t('ai.total_generations') ||
                                            'Total Generations'
                                        }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ usageStats.total_generations || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cost -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-green-500"
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
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            $t('ai.total_cost') || 'Total Cost'
                                        }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
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

                    <!-- Total Tokens -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-purple-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            $t('ai.total_tokens') ||
                                            'Total Tokens'
                                        }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{
                                            (
                                                usageStats.total_tokens || 0
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Average Cost per Generation -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-8 w-8 text-yellow-500"
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
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            $t('ai.avg_cost') || 'Avg Cost/Gen'
                                        }}
                                    </p>
                                    <p
                                        class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        ${{ getAverageCost() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage by Provider -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8"
                >
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            {{
                                $t('ai.usage_by_provider') ||
                                'Usage by Provider'
                            }}
                        </h3>

                        <div
                            v-if="
                                usageStats.by_provider &&
                                Object.keys(usageStats.by_provider).length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="(
                                    stats, provider
                                ) in usageStats.by_provider"
                                :key="provider"
                                class="border border-gray-200 dark:border-gray-600 rounded-lg p-4"
                            >
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="font-medium text-gray-900 dark:text-gray-100 capitalize"
                                    >
                                        {{ provider }}
                                    </h4>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                        >{{ stats.generations || 0 }}
                                        {{
                                            $t('ai.generations') ||
                                            'generations'
                                        }}</span
                                    >
                                </div>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.cost') || 'Cost'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >${{
                                                (stats.cost || 0).toFixed(4)
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.tokens') || 'Tokens'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                (
                                                    stats.tokens || 0
                                                ).toLocaleString()
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.avg_cost') || 'Avg Cost'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >${{
                                                getProviderAverageCost(stats)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center text-gray-500 dark:text-gray-400 py-8"
                        >
                            {{
                                $t('ai.no_usage_data') ||
                                'No usage data available'
                            }}
                        </div>
                    </div>
                </div>

                <!-- Usage by Type -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8"
                >
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            {{ $t('ai.usage_by_type') || 'Usage by Type' }}
                        </h3>

                        <div
                            v-if="
                                usageStats.by_type &&
                                Object.keys(usageStats.by_type).length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="(stats, type) in usageStats.by_type"
                                :key="type"
                                class="border border-gray-200 dark:border-gray-600 rounded-lg p-4"
                            >
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="font-medium text-gray-900 dark:text-gray-100 capitalize"
                                    >
                                        {{ type }}
                                    </h4>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                        >{{ stats.generations || 0 }}
                                        {{
                                            $t('ai.generations') ||
                                            'generations'
                                        }}</span
                                    >
                                </div>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.cost') || 'Cost'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >${{
                                                (stats.cost || 0).toFixed(4)
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.tokens') || 'Tokens'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                (
                                                    stats.tokens || 0
                                                ).toLocaleString()
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >{{
                                                $t('ai.avg_cost') || 'Avg Cost'
                                            }}:</span
                                        >
                                        <span
                                            class="ml-1 font-medium text-gray-900 dark:text-gray-100"
                                            >${{
                                                getTypeAverageCost(stats)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center text-gray-500 dark:text-gray-400 py-8"
                        >
                            {{
                                $t('ai.no_usage_data') ||
                                'No usage data available'
                            }}
                        </div>
                    </div>
                </div>

                <!-- Recent Generations -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            {{
                                $t('ai.recent_generations') ||
                                'Recent Generations'
                            }}
                        </h3>

                        <div
                            v-if="
                                recentGenerations &&
                                recentGenerations.length > 0
                            "
                            class="space-y-3"
                        >
                            <div
                                v-for="generation in recentGenerations"
                                :key="generation.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ generation.type }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ generation.provider }} •
                                        {{ formatDate(generation.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm text-gray-900 dark:text-gray-100"
                                    >
                                        ${{
                                            generation.cost_usd?.toFixed(4) ||
                                            '0.0000'
                                        }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ generation.tokens_used || 0 }}
                                        {{ $t('ai.tokens') || 'tokens' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center text-gray-500 dark:text-gray-400 py-8"
                        >
                            {{
                                $t('ai.no_recent_generations') ||
                                'No recent generations'
                            }}
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

const props = defineProps({
    usageStats: Object,
    recentGenerations: Array,
})

const getAverageCost = () => {
    if (
        !props.usageStats?.total_generations ||
        props.usageStats.total_generations === 0
    ) {
        return '0.0000'
    }
    return (
        props.usageStats.total_cost / props.usageStats.total_generations
    ).toFixed(4)
}

const getProviderAverageCost = (stats) => {
    if (!stats.generations || stats.generations === 0) {
        return '0.0000'
    }
    return (stats.cost / stats.generations).toFixed(4)
}

const getTypeAverageCost = (stats) => {
    if (!stats.generations || stats.generations === 0) {
        return '0.0000'
    }
    return (stats.cost / stats.generations).toFixed(4)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}
</script>
