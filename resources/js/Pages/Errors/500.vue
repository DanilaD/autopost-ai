<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSelector from '@/Components/LanguageSelector.vue'

const { t } = useI18n()

defineProps({
    status: {
        type: Number,
        default: 500,
    },
})
</script>

<template>
    <Head :title="t('errors.server_error')" />

    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900 relative"
    >
        <!-- Language Selector - Top Right -->
        <div class="absolute top-4 right-4 z-10">
            <LanguageSelector />
        </div>

        <div
            class="max-w-md w-full space-y-8 p-10 bg-glass-card shadow-glass-md text-center"
        >
            <!-- Error Code -->
            <div class="text-8xl font-bold text-red-600 dark:text-red-400">
                {{ status }}
            </div>

            <!-- Error Title -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ t('errors.server_error') }}
                </h1>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    {{ t('errors.server_error_description') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <Link
                    :href="route('home')"
                    class="w-full py-3 px-4 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 rounded-md inline-block"
                >
                    {{ t('errors.go_home') }}
                </Link>

                <button
                    class="w-full py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 rounded-md"
                    @click="$inertia.reload()"
                >
                    {{ t('errors.try_again') }}
                </button>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>{{ t('errors.contact_support') }}</p>
            </div>
        </div>
    </div>
</template>
