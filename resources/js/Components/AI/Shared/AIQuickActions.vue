<template>
    <div class="flex items-center space-x-2">
        <!-- Analytics Button -->
        <Link
            :href="route('ai.analytics')"
            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <ChartBarIcon class="w-4 h-4 mr-2" />
            {{ $t('ai.analytics') }}
        </Link>

        <!-- Test Providers Button -->
        <button
            :disabled="testing"
            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="testProviders"
        >
            <WrenchScrewdriverIcon class="w-4 h-4 mr-2" />
            {{ testing ? $t('ai.testing') : $t('ai.test_providers') }}
        </button>

        <!-- Page-specific actions -->
        <slot />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ChartBarIcon, WrenchScrewdriverIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    page: {
        type: String,
        required: true,
    },
})

const testing = ref(false)

const testProviders = async () => {
    testing.value = true
    try {
        await router.post(
            route('ai.test.providers'),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Success feedback handled by backend
                },
                onError: (errors) => {
                    console.error('Provider test failed:', errors)
                },
                onFinish: () => {
                    testing.value = false
                },
            }
        )
    } catch (error) {
        console.error('Provider test error:', error)
        testing.value = false
    }
}
</script>
