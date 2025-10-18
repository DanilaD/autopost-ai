<template>
    <div
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
    >
        <div class="p-6">
            <h3
                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
            >
                {{ $t('ai.recent_generations') }}
            </h3>

            <!-- History List -->
            <div v-if="history.length > 0" class="space-y-3">
                <div
                    v-for="item in history.slice(0, 5)"
                    :key="item.id"
                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                    @click="loadHistoryItem(item)"
                >
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                        >
                            {{ item.prompt }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ item.type }} • {{ formatDate(item.created_at) }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 ml-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ item.provider }}
                        </span>
                        <span
                            v-if="item.cost"
                            class="text-xs text-green-600 dark:text-green-400"
                        >
                            ${{ item.cost }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-8">
                <ClockIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3
                    class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                >
                    {{ $t('ai.no_recent_generations') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('ai.generate_content_to_see_history') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ClockIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    history: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['load-item'])

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

    if (diffInHours < 1) {
        return 'Just now'
    } else if (diffInHours < 24) {
        return `${diffInHours}h ago`
    } else {
        return date.toLocaleDateString()
    }
}

const loadHistoryItem = (item) => {
    emit('load-item', item)
}
</script>
